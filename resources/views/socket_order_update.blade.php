<!DOCTYPE html>
<html>
<head>
    <title>Socket Order Update</title>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
</head>
<body>

<h1>Socket Order Update Listener</h1>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

<script>
    // তোমার socket server URL
    const socket = io("https://union-socket.jplink.space", {
        transports: ['websocket'],
        secure: true
    });

    let user_id = "4";

    socket.emit("join-room", user_id);

    console.log("Joined Room:", user_id);

    // Rider Accept Response Listener
    socket.on("orderStatusUpdate", function(response) {
        console.log("Order Status Response:", response);
    });
</script>


</body>
</html>
