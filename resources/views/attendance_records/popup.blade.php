<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            /* Light Theme (Fresh Light Green) - Default */
            --bg-primary: #ffffff;
            --bg-secondary: #c3e6cb; /* Hover green */
            --bg-tertiary: #ffffff;
            --bg-quaternary: #f0fdf4; /* Very pale green for alternating rows */
            --bg-header: #d1e7dd; /* Fresh light green header */
            --text-primary: #052c18; /* Darker green/black for better visibility */
            --text-secondary: #0f5132;
            --border-color: #badbcc; /* Soft green border */
            --border-header: #a3cfbb;
            --input-bg: #ffffff;
            --input-text: #212529;
            --input-border: #badbcc;
            --btn-primary: #198754;
            --btn-text: #fff;
            --primary: #198754;
        }
        body {
            background: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
        }
        /* Override some styles for popup */
        .btn-toolbar {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid var(--border-color);
            padding: 15px;
            margin: 0;
            border-radius: 0;
        }
    </style>
</head>
<body>
    @include('attendance_records.form_partial')
</body>
</html>
