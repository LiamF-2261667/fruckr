<head>
    <!-- Title -->
    <title><?php echo $recipientName; ?> - Chat Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/chat.css">

    <!-- info for js -->
    <p class="hidden" id="recipientName"><?php echo $recipientName; ?></p>
</head>
<body>

<!-- Actual page-related content -->
<main>
    <!-- Page Title -->
    <h1>Chat</h1>
    <hr>

    <!-- Chat messages -->
    <ul id="chatMessages" class="custom-scrollbar">
        <!-- Privacy warning -->
        <div class="alert alert-warning">
            Chat messages are not encrypted.
            <br>
            <strong>Do not share sensitive information!</strong>
        </div>

        <!-- Displaying each chat message -->
        <?php
        $previousMsgDate = null;

        foreach ($chat->getMessages() as $message) {
            // If the message is from a different day, add a date separator
            $currentMsgDate = $message->getTimestamp()->format('d/m/Y');
            if ($currentMsgDate != $previousMsgDate) {
                echo '<li class="date-separator"><span>' . $currentMsgDate . '</span></li>';
                $previousMsgDate = $currentMsgDate;
            }

            // Add the message
            echo '<li class="message"> 
                    ' . $message->toHtml($viewerIsClient, $recipientName) . '
                  </li>';
        }
        ?>
    </ul>

    <!-- Chat input -->
    <form id="chatInput">
        <textarea class="chat-input-text" id="contentInput" name="contentInput" placeholder="Type a message..."></textarea>
        <button class="msg-submit-button" type="submit">
            <img class="icon send-icon" src="/Images/send.png" alt="Send Icon">
            <p>Send</p>
        </button>
    </form>
    <p id="maxMsgLength">0/500</p>

    <div class="alert alert-danger" id="messageErrorBox">
        Something went wrong!
    </div>

</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/chat.js"></script>