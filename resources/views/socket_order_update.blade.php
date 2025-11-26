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
    const socket = io("https://union-socket.jplink.space", {
        transports: ['websocket'],
        secure: true
    });

    // User ID from Blade
    let user_id = "4";

    // FIXED: correct event name
    socket.emit("register_user", user_id);

    console.log("User Registered:", user_id);

    socket.on("orderStatusUpdate", function(response) {
        console.log("Order Status Response:", response);
    });
</script>



</body>
</html>
