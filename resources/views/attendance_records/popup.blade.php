<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        /* Select2 customization */
        .select2-container--default .select2-selection--single {
            height: 36px;
            border: 1px solid var(--input-border);
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            color: var(--input-text);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }
        .select2-dropdown {
            border-color: var(--input-border);
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--input-border);
            border-radius: 4px;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary);
        }
    </style>
</head>
<body>
    @include('attendance_records.form_partial')
    
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#employee_select').select2({
                placeholder: 'Type to search employee...',
                allowClear: true,
                width: '300px'
            });
        });
    </script>
</body>
</html>
