
<nav>
    <ul class="menu">
        <li><a href="index.php">Home</a></li>
        <li><a href="task1.php">Task 1</a></li>
        <li><a href="task2.php">Task 2</a></li>
        <li><a href="task3.php">Task 3</a></li>
        <li><a href="task4.php">Task 4</a></li>
    </ul>
</nav>

<style>
    .menu {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        background-color: #40e90d;
        border-radius: 5px;
    }

    .menu li {
        margin: 0;
    }

    .menu li a {
        display: block;
        padding: 15px 25px;
        color: white;
        text-decoration: none;
        font-family: Arial, Helvetica, sans-serif;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .menu li a:hover {
        background-color: #09aa00;
    }

    .menu li a.active {
        background-color: #0acc00;
    }
</style>