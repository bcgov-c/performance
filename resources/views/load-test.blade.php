<!-- resources/views/session-expired.blade.php -->
<html>
<head>
    <title>Load Test</title>
</head>
<body>
<form action="{{ url('/simulate-load') }}" method="post">
    @csrf
    <label for="number_of_users">Number of Users:</label>
    <input type="number" name="number_of_users" id="number_of_users" value="1">

    <p>

    <label for="limit">Number of Rows:</label>
    <input type="number" name="limit" id="limit" value="10">

    <p>

    <button type="submit">Simulate Load</button>
</form>

</body>
</html>