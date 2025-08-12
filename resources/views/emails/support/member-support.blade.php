<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Member Support Request</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        <h2>New Member Support Request</h2>
        <p>A new member support request has been submitted through the mobile app.</p>
    </div>
    
    <div class="content">
        <!-- Member Name -->
        <div class="field">
            <div class="field-label">Member Name:</div>
            <div class="field-value">{{ $userName }}</div>
        </div>
        
        <!-- Email Address -->
        <div class="field">
            <div class="field-label">Email Address:</div>
            <div class="field-value">
                <a href="mailto:{{ $userEmail }}">{{ $userEmail }}</a>
            </div>
        </div>
        
        <!-- Support Message -->
        <div class="field">
            <div class="field-label">How can we support you?</div>
            <div class="field-value">{{ $userMessage }}</div>
        </div>
        
        <!-- Submission Details -->
        <div class="field">
            <div class="field-label">Submitted At:</div>
            <div class="field-value">{{ $submittedAt->format('F j, Y \a\t g:i A T') }}</div>
        </div>
        
        <!-- Form ID for reference -->
        <div class="field">
            <div class="field-label">Reference ID:</div>
            <div class="field-value">#{{ $supportForm->id }}</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This email was automatically generated from the MA12 mobile app support form.</p>
        <p>Please respond directly to the member's email address: {{ $userEmail }}</p>
    </div>
</body>
</html>
