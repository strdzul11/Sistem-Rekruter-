<?php
namespace App;

use App\Config\DatabaseConnection as Database;
use Exception;
use PDO;
use PDOException;

class CalendarIntegration {
    private $db;
    private $googleClientId;
    private $googleClientSecret;
    private $googleRedirectUri;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Google Calendar API Configuration
        $this->googleClientId = 'YOUR_GOOGLE_CLIENT_ID';
        $this->googleClientSecret = 'YOUR_GOOGLE_CLIENT_SECRET';
        $this->googleRedirectUri = 'http://localhost/rekruter/auth/google_callback.php';
    }
    
    // Google Calendar Integration
    public function getGoogleAuthUrl($userId) {
        $state = base64_encode(json_encode(['user_id' => $userId, 'timestamp' => time()]));
        
        $params = [
            'client_id' => $this->googleClientId,
            'redirect_uri' => $this->googleRedirectUri,
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    public function handleGoogleCallback($code, $state) {
        try {
            $stateData = json_decode(base64_decode($state), true);
            $userId = $stateData['user_id'];
            
            // Exchange code for tokens
            $tokenData = $this->exchangeCodeForTokens($code);
            
            if ($tokenData) {
                // Save tokens to database
                $this->saveCalendarIntegration($userId, 'google', $tokenData);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error handling Google callback: " . $e->getMessage());
            return false;
        }
    }
    
    private function exchangeCodeForTokens($code) {
        $data = [
            'client_id' => $this->googleClientId,
            'client_secret' => $this->googleClientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->googleRedirectUri
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return false;
    }
    
    private function saveCalendarIntegration($userId, $type, $tokenData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO calendar_integrations 
                (user_id, integration_type, access_token, refresh_token, expires_at) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                expires_at = VALUES(expires_at),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
            
            return $stmt->execute([
                $userId,
                $type,
                $tokenData['access_token'],
                $tokenData['refresh_token'],
                $expiresAt
            ]);
        } catch (PDOException $e) {
            error_log("Error saving calendar integration: " . $e->getMessage());
            return false;
        }
    }
    
    // Create Google Calendar Event
    public function createGoogleCalendarEvent($userId, $interviewData) {
        try {
            $integration = $this->getCalendarIntegration($userId, 'google');
            if (!$integration) {
                return false;
            }
            
            // Check if token is expired and refresh if needed
            if (strtotime($integration['expires_at']) <= time()) {
                $integration = $this->refreshGoogleToken($integration);
                if (!$integration) {
                    return false;
                }
            }
            
            $eventData = [
                'summary' => 'Interview: ' . $interviewData['applicant_name'] . ' - ' . $interviewData['position'],
                'description' => 'Interview for position: ' . $interviewData['position'] . ' at ' . $interviewData['company'] . "\n\n" . 
                                'Applicant: ' . $interviewData['applicant_name'] . "\n" .
                                'Email: ' . $interviewData['applicant_email'] . "\n" .
                                'Phone: ' . $interviewData['applicant_phone'] . "\n\n" .
                                'Notes: ' . ($interviewData['notes'] ?? ''),
                'start' => [
                    'dateTime' => $interviewData['interview_date'],
                    'timeZone' => $interviewData['timezone']
                ],
                'end' => [
                    'dateTime' => date('c', strtotime($interviewData['interview_date'] . ' +' . $interviewData['duration'] . ' minutes')),
                    'timeZone' => $interviewData['timezone']
                ],
                'attendees' => [
                    ['email' => $interviewData['applicant_email']],
                    ['email' => $interviewData['interviewer_email']]
                ],
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => 'interview_' . $interviewData['interview_id'] . '_' . time(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                    ]
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/calendar/v3/calendars/primary/events?conferenceDataVersion=1');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($eventData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $integration['access_token'],
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $event = json_decode($response, true);
                return $event['id'];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error creating Google Calendar event: " . $e->getMessage());
            return false;
        }
    }
    
    // Update Google Calendar Event
    public function updateGoogleCalendarEvent($userId, $eventId, $interviewData) {
        try {
            $integration = $this->getCalendarIntegration($userId, 'google');
            if (!$integration) {
                return false;
            }
            
            // Check if token is expired and refresh if needed
            if (strtotime($integration['expires_at']) <= time()) {
                $integration = $this->refreshGoogleToken($integration);
                if (!$integration) {
                    return false;
                }
            }
            
            $eventData = [
                'summary' => 'Interview: ' . $interviewData['applicant_name'] . ' - ' . $interviewData['position'],
                'description' => 'Interview for position: ' . $interviewData['position'] . ' at ' . $interviewData['company'] . "\n\n" . 
                                 'Applicant: ' . $interviewData['applicant_name'] . "\n" .
                                 'Email: ' . $interviewData['applicant_email'] . "\n" .
                                 'Phone: ' . $interviewData['applicant_phone'] . "\n\n" .
                                 'Notes: ' . ($interviewData['notes'] ?? ''),
                'start' => [
                    'dateTime' => $interviewData['interview_date'],
                    'timeZone' => $interviewData['timezone']
                ],
                'end' => [
                    'dateTime' => date('c', strtotime($interviewData['interview_date'] . ' +' . $interviewData['duration'] . ' minutes')),
                    'timeZone' => $interviewData['timezone']
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/calendar/v3/calendars/primary/events/' . $eventId);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($eventData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $integration['access_token'],
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
        } catch (Exception $e) {
            error_log("Error updating Google Calendar event: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete Google Calendar Event
    public function deleteGoogleCalendarEvent($userId, $eventId) {
        try {
            $integration = $this->getCalendarIntegration($userId, 'google');
            if (!$integration) {
                return false;
            }
            
            // Check if token is expired and refresh if needed
            if (strtotime($integration['expires_at']) <= time()) {
                $integration = $this->refreshGoogleToken($integration);
                if (!$integration) {
                    return false;
                }
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/calendar/v3/calendars/primary/events/' . $eventId);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $integration['access_token']
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
        } catch (Exception $e) {
            error_log("Error deleting Google Calendar event: " . $e->getMessage());
            return false;
        }
    }
    
    private function getCalendarIntegration($userId, $type) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM calendar_integrations 
                WHERE user_id = ? AND integration_type = ? AND is_active = 1
            ");
            $stmt->execute([$userId, $type]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting calendar integration: " . $e->getMessage());
            return false;
        }
    }
    
    private function refreshGoogleToken($integration) {
        try {
            $data = [
                'client_id' => $this->googleClientId,
                'client_secret' => $this->googleClientSecret,
                'refresh_token' => $integration['refresh_token'],
                'grant_type' => 'refresh_token'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $tokenData = json_decode($response, true);
                
                // Update tokens in database
                $stmt = $this->db->prepare("
                    UPDATE calendar_integrations 
                    SET access_token = ?, expires_at = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                
                $expiresAt = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
                $stmt->execute([$tokenData['access_token'], $expiresAt, $integration['id']]);
                
                $integration['access_token'] = $tokenData['access_token'];
                $integration['expires_at'] = $expiresAt;
                
                return $integration;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error refreshing Google token: " . $e->getMessage());
            return false;
        }
    }
    
    // Outlook Calendar Integration (Basic implementation)
    public function createOutlookCalendarEvent($userId, $interviewData) {
        // Implementation for Outlook Calendar integration
        // This would require Microsoft Graph API integration
        return false;
    }
    
    // Get Available Time Slots from Calendar
    public function getAvailableTimeSlots($userId, $date, $timezone = 'Asia/Jakarta') {
        try {
            $integration = $this->getCalendarIntegration($userId, 'google');
            if (!$integration) {
                return false;
            }
            
            $startTime = $date . 'T00:00:00' . $this->getTimezoneOffset($timezone);
            $endTime = $date . 'T23:59:59' . $this->getTimezoneOffset($timezone);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/calendar/v3/freeBusy');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'timeMin' => $startTime,
                'timeMax' => $endTime,
                'items' => [['id' => 'primary']]
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $integration['access_token'],
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return $this->parseBusyTimes($data['calendars']['primary']['busy'] ?? []);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error getting available time slots: " . $e->getMessage());
            return false;
        }
    }
    
    private function getTimezoneOffset($timezone) {
        $offsets = [
            'Asia/Jakarta' => '+07:00',
            'Asia/Makassar' => '+08:00',
            'Asia/Jayapura' => '+09:00'
        ];
        
        return $offsets[$timezone] ?? '+07:00';
    }
    
    private function parseBusyTimes($busyTimes) {
        $availableSlots = [];
        $startTime = strtotime('09:00:00');
        $endTime = strtotime('17:00:00');
        
        // Generate 30-minute slots
        for ($time = $startTime; $time <= $endTime; $time += 1800) {
            $slotTime = date('Y-m-d H:i:s', $time);
            $isAvailable = true;
            
            foreach ($busyTimes as $busy) {
                $busyStart = strtotime($busy['start']);
                $busyEnd = strtotime($busy['end']);
                
                if ($time >= $busyStart && $time < $busyEnd) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $availableSlots[] = [
                    'datetime' => $slotTime,
                    'formatted' => date('H:i', $time)
                ];
            }
        }
        
        return $availableSlots;
    }
}
