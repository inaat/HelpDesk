<!DOCTYPE html>
<html lang="ar" dir="rtl"> <!-- Setting the direction to RTL and language to Arabic -->
<head>
    <meta charset="UTF-8">
    <title>Email</title>
</head>
<body>
    <h1>
        تم فتح تذكرة رقم #{{ $ticket->uid }} مع المندوب {{ $ticket->assignedTo->first_name }}
    </h1> <!-- The message will follow the RTL direction -->
</body>
</html>
