<!DOCTYPE html>
<html lang="en">
<head>
    <title>Server error &ndash; {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/4.4.0/css/bootstrap.css">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body>
<nav class="navbar navbar-dark navbar-expand-lg bg-dark">
    <a class="navbar-brand" href="/">{{ config('app.name') }}</a>
</nav>

<div class="container mt-4">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading"><b>Uh-oh.</b> UTRS has suffered an internal exception and can't continue to serve your request.</h4>

        <p>We apologise for any inconvenience caused. Please report this exception at <a href="https://github.com/utrs2/utrs/issues" class="alert-link">https://github.com/utrs2/utrs/issues</a>.</p>
        <p>
            If this happened while creating your appeal, do not create another appeal as it might have been saved.
            Instead, contact UTRS developers at <a href="mailto:utrs-developers@googlegroups.com" class="alert-link">utrs-developers@googlegroups.com</a>
            so they can help you.
        </p>
    </div>
</div>
</body>
</html>
