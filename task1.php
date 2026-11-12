<!DOCTYPE html>
<html lang="en">
<!--========================================================
// ICT3612 Assignment - Task 1
// Database Design
// 17945968
// Emmanuel Mpwanga
-========================================================-->

<head>

    <meta charset="UTF-8">
    <title>ICT3612 Assignment 3 - Task 1 17945968 Emmanuel Mpwanga</title>

    <style>
        /* style for the body  background and text font */
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        /* style for the header (sandard text colour)  */
        h1,
        h2,
        h3 {
            color: #40e90d;
            ;

        }

        /* style for table (border and background) */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
        }

        /* style for table cells (hearder,data) */
        table,
        th,
        td {
            border: 1px solid black;
        }

        th {
            background: #40e90d;
            color: white;
            padding: 10px;
        }

        td {
            padding: 10px;
            vertical-align: top;
        }

        /* style for sections to split both tasks */
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0px 0px 8px lightgray;
        }

        /* style for image of ERD Diagram */
        img {
            display: block;
            margin: auto;
            border: 1px solid gray;
        }

        /* style for ifram to show txt file */
        iframe {
            display: block;
            margin: auto;
            border: 2px solid #40e90d;
        }
    </style>

</head>

<body>
    <!-- Include the navigation menu -->
    <?php include 'menu.inc'; ?>

    <!-- Page Title -->
    <h1>ICT3612 Assignment 3</h1>
    <h2>Task 1</h2>

    <!-- Task 1(a) Section -->
    <div class="section">

        <h3>//////////////////////////////Task 1(a) //////////////////////////</h3>
        <!-- Table answering question 1(a) -->
        <table>

            <tr>
                <th>Table</th>
                <th>Entity Represented</th>
                <th>Primary Key</th>
                <th>Foreign Keys</th>
            </tr>

            <tr>

                <td><b>Modules</b></td>

                <td>

                    Entity represented: The Modules table stores information about the academic modules
                    offered by the institution. Each record represents one module.
                    Attributes
                    - ModuleCode : The special code for the Module (can not be duplicate)
                    - ModuleName : The name of the Module

                </td>

                <td>

                    ModuleCode

                </td>

                <td>

                    None

                </td>

            </tr>

            <tr>

                <td><b>Lecturers</b></td>

                <td>

                    Entity represented: The Lecturers table stores information about lecturers who teach modules.
                    Attributes
                    - LecturerID : The special ID for the Lecturer (can not be duplicate)
                    - LecturerName : The name of the Lecturer

                </td>

                <td>

                    LecturerID

                </td>

                <td>

                    None

                </td>

            </tr>

            <tr>

                <td><b>Roles</b></td>

                <td>

                    Entity represented: The Roles table stores the different responsibilities lecturers may have for a
                    module.
                    Attributes
                    - RoleID : The special ID for the role (can not be duplicate)
                    - RoleName : The name for the role the Lecturer does for the module

                    Examples:

                    <!-- list showing different roles lecturers can have -->

                    <ul>

                        <li>Primary Lecturer</li>

                        <li>Secondary Lecturer</li>

                        <li>Internal Moderator</li>

                        <li>External Moderator</li>

                        <li>Critical Reader</li>

                    </ul>

                </td>

                <td>

                    RoleID

                </td>

                <td>

                    None

                </td>

            </tr>

            <tr>

                <td><b>ModuleLecturerRole</b></td>

                <td>

                    The ModuleLecturerRole table is an joint table that links modules, lecturers, and roles together.
                    It records which lecturer performs which role for a specific module during a particular year.
                    Attributes
                    - ModuleCode : The special code for the Module (can not be duplicate)
                    - LecturerID : The special ID for the Lecturer (can not be duplicate)
                    - RoleID : The special ID for the role (can not be duplicate)
                    - Year : The year that the Lecturer is doing the module


                </td>

                <td>

                    <ul>

                        <li>ModuleCode</li>

                        <li>LecturerID</li>

                        <li>RoleID</li>

                    </ul>

                </td>

                <td>

                    <ul>

                        <li>ModuleCode → Modules(ModuleCode)</li>

                        <li>LecturerID → Lecturers(LecturerID)</li>

                        <li>RoleID → Roles(RoleID)</li>

                    </ul>

                </td>

            </tr>

        </table>

    </div>


    <!-- Question 1(b) Section -->


    <div class="section">

        <h3>//////////////////////////////Task 1(b) //////////////////////////</h3>

        <h3>Database Design for Users, Events and Registrations</h3>

        <p>

            Each user can register for many events, and each event can have many users.
            The Registrations table resolves this many-to-many relationship.

        </p>

        <!-- Screenshot of ER Diagram -->

        <img src="Screenshot_28-7-2026_20409_online.visual-paradigm.com.jpeg" width="850" alt="ER Diagram">

        <!-- Explanation of the ER Diagram -->
        <br><br>

        <table>

            <tr>

                <th>Table</th>

                <th>Description</th>

                <th>Primary Key</th>

                <th>Foreign Keys</th>

            </tr>

            <tr>

                <td>Users</td>

                <td>

                    Stores information about users who attend events.
                    Attributes
                    - UserID
                    - FirstName
                    - LastName
                    - Email
                    - Phone

                </td>

                <td>UserID</td>

                <td>None</td>

            </tr>

            <tr>

                <td>Events</td>

                <td>

                    Stores details of events available for registration.
                    Attributes
                    - EventID
                    - EventName
                    - EventDate
                    - Venue
                    - Cost
                </td>

                <td>EventID</td>

                <td>None</td>

            </tr>

            <tr>

                <td>Registrations</td>

                <td>

                    Stores the registrations made by users. A user may register for many events. An event may have many
                    users. This table resolves the many-to-many relationship.
                    Attributes
                    - RegistrationID
                    - UserID
                    - EventID
                    - RegistrationDate
                </td>

                <td>RegistrationID</td>

                <td>

                    UserID → Users(UserID)

                    <br>

                    EventID → Events(EventID)

                </td>

            </tr>

        </table>

    </div>


    <h2 style="text-align:center;">PHP Source Code</h2>

    <center>


        <!-- Iframe for PHP Source Code -->
        <iframe src="task1.txt" height="400" width="1200">

            Your browser does not support iframes.

        </iframe>

    </center>

</body>

</html>