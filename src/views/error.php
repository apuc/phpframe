<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?></title>
    <style>
        body {
            font-family: Roboto, sans-serif;
            padding: 0 25px;
        }
        pre {
            font-family: "Roboto Mono", monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
<h1><?php echo $title ?></h1>
<?php if ($debug) { ?>
    <pre><?php echo $message ?></pre>
<?php } ?>
</body>
</html>
