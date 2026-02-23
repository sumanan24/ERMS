<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Unavailable - University College of Jaffna</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 20px;
        }
        .box {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 2.5rem;
            max-width: 420px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
        }
        h1 { font-size: 1.5rem; margin-bottom: 0.75rem; }
        p { opacity: 0.95; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Site temporarily unavailable</h1>
        <p>This page isn't able to handle your request right now. Please try again in a few minutes.</p>
        <p style="margin-top: 1rem; font-size: 0.9rem;">If the problem continues, contact your system administrator.</p>
        <?php if (!empty($db_error)): ?>
        <p style="margin-top: 1rem; padding: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 6px; font-size: 0.85rem; word-break: break-all;">Database: <?php echo htmlspecialchars($db_error); ?></p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; opacity: 0.8;">Remove <code>?debug=1</code> from the URL after fixing.</p>
        <?php endif; ?>
    </div>
</body>
</html>
