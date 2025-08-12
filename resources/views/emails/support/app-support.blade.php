<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New App Support Issue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #495057;
        }
        .field-value {
            margin-top: 5px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .priority {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🚨 New App Support Issue</h2>
        <p>A new app support issue has been reported through the mobile app.</p>
    </div>
    
    <div class="priority">
        <strong>⚠️ Priority:</strong> App issues may affect user experience and should be reviewed promptly.
    </div>
    
    <div class="content">
        <!-- User Name -->
        <div class="field">
            <div class="field-label">Reporter Name:</div>
            <div class="field-value">{{ $userName }}</div>
        </div>
        
        <!-- Email Address -->
        <div class="field">
            <div class="field-label">Email Address:</div>
            <div class="field-value">
                <a href="mailto:{{ $userEmail }}">{{ $userEmail }}</a>
            </div>
        </div>
        
        <!-- Issue Description -->
        <div class="field">
            <div class="field-label">Issue Description:</div>
            <div class="field-value">{{ $userMessage }}</div>
        </div>
        
        <!-- Submission Details -->
        <div class="field">
            <div class="field-label">Reported At:</div>
            <div class="field-value">{{ $submittedAt->format('F j, Y \a\t g:i A T') }}</div>
        </div>
        
        <!-- Ticket ID for reference -->
        <div class="field">
            <div class="field-label">Ticket ID:</div>
            <div class="field-value">#APP-{{ $supportForm->id }}</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This email was automatically generated from the MA12 mobile app support form.</p>
        <p>Please investigate the issue and respond to the user at: {{ $userEmail }}</p>
        <p><strong>Tip:</strong> Consider asking for device information, app version, and steps to reproduce if needed.</p>
    </div>
</body>
</html>
