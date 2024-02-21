<head>
    <!-- Title -->
    <title>Staff | <?php echo $foodtruck->getName(); ?> - Fruckr</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/staff.css">
</head>
<body>

<!-- Actual page-related content -->
<main>
    <!-- The page title -->
    <h1>Manage Staff</h1>
    <h2>For <?php echo $foodtruck->getName(); ?></h2>

    <hr>

    <!-- Loading icon for deleting staff members -->
    <img class="loadingCircle" id="deleteStaffLoading" src="../../Gifs/loading.gif" alt="Loading icon">

    <!-- Error box for deleting staff members -->
    <div id="deleteStaffError" class="alert alert-danger">
        Something went wrong!
    </div>

    <!-- The list of staff members -->
    <h2>Staff members</h2>
    <ul id="staffList">
        <?php
        // Display all the staff members
        foreach ($workers as $worker) {
            // Make sure to only show workers that are not the owner
            if ($worker->getUid() !== $foodtruck->getOwner()->getUid())
                echo "
                <li class='staffMember' data-uid='{$worker->getUid()}'>
                    <h2 class='name'>{$worker->getFullName()}</h2>
                    <div class='info'>
                        <p class='email'>Email: {$worker->getEmail()}</p>
                        <p class='phone'>Phone number: {$worker->getFormattedPhoneNumber()}</p>
                    </div>
                    <button class='remove-staff-btn'>Remove</button>
                </li>";
        }

        // If there are no staff members, display a message
        if (count($workers) < 2)
            echo "<p class='no-staff'>There are no staff members yet.</p>";
        ?>
    </ul>

    <hr>

    <!-- The form to add a new staff member -->
    <form id="addStaffForm">
        <h2>Add a new staff member</h2>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Email" required>
        <button type="submit">Add</button>
    </form>

    <!-- Loading icon for adding staff members -->
    <img class="loadingCircle" id="addStaffLoading" src="../../Gifs/loading.gif" alt="Loading icon">

    <!-- Success box for adding staff members -->
    <div id="addStaffSuccess" class="alert alert-success">
        Successfully added the user!
    </div>

    <!-- Error box for adding staff members -->
    <div id="addStaffError" class="alert alert-danger">
        Something went wrong!
    </div>
</main>

</body>
</html>

<!-- JavaScript -->
<script src="/js/staff.js"></script>