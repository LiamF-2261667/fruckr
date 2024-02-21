<head>
    <!-- Title -->
    <title>Chat List - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/chats.css">
</head>
<body>

<!-- Actual page-related content -->
<main>
    <!-- Page Title -->
    <h1 id="viewPointTitle">Chats for: <?php echo $viewPointName; ?></h1>

    <hr>

    <!-- Chat list -->
    <ul id="chatList">
        <?php
        foreach ($chats as $chat) {
            echo '<li class="chatItem">' . $chat->toShortHtml($fromClientView) . '</li>';
        }
        ?>
    </ul>
</main>

</body>
</html>