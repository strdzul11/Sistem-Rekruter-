# Fitur Interview Management - Sistem Rekrutmen

## 🎯 Overview

Fitur Interview Management adalah sistem lengkap untuk mengelola proses interview dalam sistem rekrutmen, termasuk penjadwalan, video interview, integrasi calendar, dan feedback management.

## 🚀 Fitur Utama

### 1. **Interview Scheduling System**
- ✅ Penjadwalan interview dengan timezone support
- ✅ Auto-generate meeting links untuk video interview
- ✅ Multiple interview types (video, phone, in-person, online)
- ✅ Duration management (15-180 menit)
- ✅ Interviewer assignment

### 2. **Google Calendar Integration**
- ✅ OAuth2 authentication dengan Google
- ✅ Auto-create calendar events
- ✅ Update dan delete events
- ✅ Timezone handling
- ✅ Attendee management

### 3. **Video Interview System**
- ✅ Built-in video conference interface
- ✅ Meeting link generation
- ✅ Interview questions library
- ✅ Real-time notes taking
- ✅ Screen sharing capabilities
- ✅ Recording functionality

### 4. **Interview Feedback System**
- ✅ Comprehensive scoring (1-5 scale)
- ✅ Multiple evaluation criteria:
  - Communication Skills
  - Technical Skills
  - Problem Solving
  - Cultural Fit
- ✅ Detailed feedback forms
- ✅ Recommendation system
- ✅ Next steps planning

### 5. **Interview Management Dashboard**
- ✅ Statistics dan analytics
- ✅ Interview status tracking
- ✅ Bulk operations
- ✅ Search dan filtering
- ✅ Export capabilities

## 📊 Database Schema

### Tabel `interview_schedules`
```sql
- id (Primary Key)
- application_id (Foreign Key)
- interviewer_id (Foreign Key)
- interview_type (ENUM: phone, video, in-person, online)
- interview_date (DATETIME)
- timezone (VARCHAR)
- duration_minutes (INT)
- meeting_link (VARCHAR)
- meeting_room (VARCHAR)
- status (ENUM: scheduled, confirmed, completed, cancelled, rescheduled)
- notes (TEXT)
- google_calendar_event_id (VARCHAR)
- outlook_calendar_event_id (VARCHAR)
```

### Tabel `interview_feedback`
```sql
- id (Primary Key)
- interview_schedule_id (Foreign Key)
- interviewer_id (Foreign Key)
- applicant_id (Foreign Key, nullable)
- communication_score (INT 1-5)
- technical_score (INT 1-5)
- problem_solving_score (INT 1-5)
- cultural_fit_score (INT 1-5)
- overall_score (DECIMAL, auto-calculated)
- strengths (TEXT)
- weaknesses (TEXT)
- areas_for_improvement (TEXT)
- recommendation (ENUM: hire, no_hire, maybe, strong_hire)
- next_steps (TEXT)
- additional_notes (TEXT)
- feedback_status (ENUM: draft, submitted, reviewed)
```

### Tabel `interview_questions`
```sql
- id (Primary Key)
- question_text (TEXT)
- question_type (ENUM: technical, behavioral, situational, cultural, general)
- difficulty_level (ENUM: easy, medium, hard)
- job_position (VARCHAR, nullable)
- is_active (BOOLEAN)
- created_by (Foreign Key)
```

### Tabel `calendar_integrations`
```sql
- id (Primary Key)
- user_id (Foreign Key)
- integration_type (ENUM: google, outlook, apple)
- access_token (TEXT)
- refresh_token (TEXT)
- calendar_id (VARCHAR)
- is_active (BOOLEAN)
- expires_at (TIMESTAMP)
```

## 🔧 Technical Implementation

### 1. **InterviewManager Class**
```php
class InterviewManager {
    // Schedule interview
    public function scheduleInterview($applicationId, $interviewerId, $interviewDate, $timezone, $interviewType, $duration, $notes)
    
    // Get interview details
    public function getInterviewDetails($interviewId)
    
    // Update interview status
    public function updateInterviewStatus($interviewId, $status, $meetingLink)
    
    // Submit feedback
    public function submitInterviewFeedback($interviewId, $interviewerId, $feedbackData)
    
    // Get available time slots
    public function getAvailableTimeSlots($interviewerId, $date, $timezone)
    
    // Generate meeting link
    public function generateMeetingLink($interviewType)
}
```

### 2. **CalendarIntegration Class**
```php
class CalendarIntegration {
    // Google Calendar OAuth
    public function getGoogleAuthUrl($userId)
    public function handleGoogleCallback($code, $state)
    
    // Calendar events
    public function createGoogleCalendarEvent($userId, $interviewData)
    public function updateGoogleCalendarEvent($userId, $eventId, $interviewData)
    public function deleteGoogleCalendarEvent($userId, $eventId)
    
    // Available time slots
    public function getAvailableTimeSlots($userId, $date, $timezone)
}
```

## 📱 User Interface

### 1. **Admin Interface** (`admin/interviews.php`)
- Dashboard dengan statistics
- Interview scheduling form
- Interview list dengan filtering
- Status management
- Calendar integration setup

### 2. **HRD Interface** (`hrd/interviews.php`)
- Simplified interface untuk HRD
- Interview scheduling
- Feedback submission
- Video interview access

### 3. **Video Interview Interface** (`admin/video_interview.php`)
- Video conference interface
- Interview questions library
- Real-time notes
- Recording capabilities
- Screen sharing

### 4. **Feedback Interface** (`admin/interview_feedback.php`)
- Comprehensive scoring form
- Detailed feedback sections
- Recommendation system
- Auto-calculated overall score

## 🔐 Security Features

### 1. **Authentication & Authorization**
- Role-based access control
- Session management
- CSRF protection

### 2. **Data Security**
- Input sanitization
- SQL injection prevention
- XSS protection
- Secure token storage

### 3. **Calendar Integration Security**
- OAuth2 secure authentication
- Token encryption
- Secure API calls

## 📈 Analytics & Reporting

### 1. **Interview Statistics**
- Total interviews scheduled
- Completion rate
- Average scores
- Cancellation rate

### 2. **Performance Metrics**
- Interviewer performance
- Time to hire
- Interview success rate
- Feedback quality

### 3. **Dashboard Widgets**
- Real-time statistics
- Upcoming interviews
- Recent feedback
- Performance trends

## 🚀 Setup Instructions

### 1. **Database Setup**
```bash
# Run the database update
http://localhost/rekruter/update_interview_database.php
```

### 2. **Google Calendar Integration**
1. Create Google Cloud Project
2. Enable Calendar API
3. Create OAuth2 credentials
4. Update `includes/calendar_integration.php` with credentials
5. Set redirect URI: `http://localhost/rekruter/auth/google_callback.php`

### 3. **File Permissions**
```bash
chmod 755 uploads/
chmod 644 logs/applications.log
```

## 📋 Usage Guide

### 1. **Scheduling Interview**
1. Go to Interview Management
2. Click "Jadwalkan Interview"
3. Select application and interviewer
4. Set date, time, and timezone
5. Choose interview type
6. Add notes if needed
7. Submit

### 2. **Conducting Video Interview**
1. Click on video interview link
2. Join meeting room
3. Use question library
4. Take notes
5. Record if needed
6. End interview

### 3. **Submitting Feedback**
1. Go to interview feedback page
2. Rate each criteria (1-5)
3. Fill detailed feedback
4. Set recommendation
5. Add next steps
6. Submit feedback

## 🔄 Workflow

### 1. **Interview Lifecycle**
```
Application → Schedule Interview → Conduct Interview → Submit Feedback → Update Application Status
```

### 2. **Status Flow**
```
not_scheduled → scheduled → confirmed → completed
                ↓
            cancelled/rescheduled
```

### 3. **Feedback Flow**
```
draft → submitted → reviewed
```

## 🎯 Best Practices

### 1. **Interview Scheduling**
- Schedule interviews at least 24 hours in advance
- Send calendar invites to all participants
- Include meeting links for video interviews
- Set appropriate duration based on interview type

### 2. **Feedback Quality**
- Provide specific examples in feedback
- Use consistent scoring criteria
- Include actionable recommendations
- Document next steps clearly

### 3. **Video Interview**
- Test technology before interview
- Have backup communication method
- Record important interviews
- Use structured question format

## 🐛 Troubleshooting

### 1. **Common Issues**
- **Calendar integration not working**: Check OAuth credentials
- **Meeting links not generating**: Verify database connection
- **Video not working**: Check browser permissions
- **Feedback not saving**: Verify form validation

### 2. **Error Messages**
- `Interview scheduling failed`: Check database connection
- `Calendar event creation failed`: Verify Google API credentials
- `Meeting link generation failed`: Check interview type
- `Feedback submission failed`: Validate form data

## 🔮 Future Enhancements

### 1. **Planned Features**
- AI-powered interview analysis
- Automated scheduling with AI
- Video interview transcription
- Advanced analytics dashboard
- Mobile app integration

### 2. **Technical Improvements**
- WebRTC implementation for video
- Real-time collaboration
- Advanced calendar integrations
- API endpoints for external systems

## 📞 Support

For technical support or feature requests:
- Check documentation first
- Review error logs
- Contact system administrator
- Submit issue with detailed description

---

**Fitur Interview Management** - Membuat proses rekrutmen lebih efisien dan terstruktur dengan sistem interview yang lengkap dan terintegrasi.
