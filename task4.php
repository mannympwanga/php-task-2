<?php
/*****************************************************************
 * ICT3612 Assignment - Task 4
 * Employee Training Management Systems
 * 17945968
 * Emmanuel Mpwanga
 *****************************************************************/

/*****************************************************************
 * CONFIGURATION
 *****************************************************************/
/*
 * Start session and enable error reporting
 */
session_start();

error_reporting(E_ALL);
ini_set("display_errors", 1);

/*****************************************************************
 * DATABASE CONFIGURATION
 *****************************************************************/

/*
 * Database connection settings
 */
define("DB_HOST", "sql109.infinityfree.com");
define("DB_USER", "if0_42540835");
define("DB_PASS", "UBlzf5dJxyjLy");
define("DB_NAME", "if0_42540835_task4");

/*****************************************************************
 * DATABASE CLASS
 *****************************************************************/

class Database
{
    /**************************************************************
     * Database Connection
     **************************************************************/
    private static $connection = null;

    public static function getConnection()
    {
        /* If connection is not established, create a new one */
        if (self::$connection === null) {

            /* Establish a new database connection */
            self::$connection = mysqli_connect(

                DB_HOST,

                DB_USER,

                DB_PASS,

                DB_NAME

            );

            /* Check if the connection was successful */
            if (!self::$connection) {

                die(

                    "Connection Failed : "

                    .

                    mysqli_connect_error()

                );

            }

            mysqli_set_charset(

                self::$connection,

                "utf8"

            );

        }

        return self::$connection;

    }

}

/*****************************************************************
 * BASE MODEL
 *****************************************************************/

/*
 * Base model class for all models to extend
 */
abstract class BaseModel
{
    protected $db;

    /**************************************************************
     * Constructor
     **************************************************************/
    public function __construct()
    {

        $this->db =

            Database::getConnection();

    }

}

/*****************************************************************
 * GENERAL HELPER FUNCTIONS
 *****************************************************************/

/**************************************************************
 * Clean Input
 **************************************************************/
function clean($value)
{
    /* Trim whitespace and escape special characters to prevent XSS */
    return htmlspecialchars(

        trim($value),

        ENT_QUOTES,

        "UTF-8"

    );

}

/**************************************************************
 * Redirect
 **************************************************************/
function redirect($page)
{
    /* Redirect to the specified page */
    header(

        "Location: task4.php?page=" . $page

    );

    exit();

}

/*****************************************************************
 * SESSION FLASH MESSAGES
 *****************************************************************/

/**************************************************************
 * Set Flash Message
 **************************************************************/
function setFlash(

    /* Set a flash message to be displayed on the next page load */
    $message,

    $type = "success"

) {

    $_SESSION["flash_message"] = $message;

    $_SESSION["flash_type"] = $type;

}


function getFlash()
{
    /* Retrieve and clear the flash message from the session */
    if (isset($_SESSION["flash_message"])) {

        $message = array(

            "text" => $_SESSION["flash_message"],

            "type" => $_SESSION["flash_type"]

        );

        unset($_SESSION["flash_message"]);

        unset($_SESSION["flash_type"]);

        return $message;

    }

    return null;

}

/*****************************************************************
 * AUTHENTICATION HELPERS
 *****************************************************************/

function loggedIn()
{
    /* Check if a user is logged in by verifying the session */
    return isset($_SESSION["user"]);

}

function adminLoggedIn()
{
    /* Check if the logged-in user is an admin */
    if (!loggedIn()) {

        return false;

    }

    return

        $_SESSION["user"]["Role"]

        ==

        "ADMIN";

}

function companyLoggedIn()
{
    /* Check if the logged-in user is a company user */
    if (!loggedIn()) {

        return false;

    }

    return

        $_SESSION["user"]["Role"]

        ==

        "COMPANY";

}

function currentCompanyID()
{
    /* Get the CompanyID of the currently logged-in company user, or return 0 if not logged in as a company */
    if (companyLoggedIn()) {

        return

            $_SESSION["user"]["CompanyID"];

    }

    return 0;

}

function logout()
{
    /* Log out the current user by clearing the session and redirecting to the login page */
    $_SESSION = array();

    session_destroy();

    redirect("login");

}

/*****************************************************************
 * VALIDATION
 *****************************************************************/

function required($value)
{
    /* Check if a value is not empty after trimming whitespace */
    return trim($value) != "";

}

function validEmail($email)
{
    /* Validate an email address using PHP's filter_var function */
    return

        filter_var(

            $email,

            FILTER_VALIDATE_EMAIL

        );

}

function hashPassword($password)
{
    /* Hash a password using PHP's password_hash function with the default algorithm */
    return password_hash(

        $password,

        PASSWORD_DEFAULT

    );

}

function verifyPassword(
    /* Verify a password against a hashed password using PHP's password_verify function */
    $password,

    $hash

) {

    return

        password_verify(

            $password,

            $hash

        );

}

/*****************************************************************
 * COMMON DATA
 *****************************************************************/

$page = "home";

/* Get the requested page from the query string, if provided */
if (isset($_GET["page"])) {

    $page =

        clean($_GET["page"]);

}

$action = "";

if (isset($_GET["action"])) {

    $action =

        clean($_GET["action"]);

}

/*****************************************************************
 * LOGOUT
 *****************************************************************/
/* If the action is "logout", call the logout function to log out the user */
if ($action == "logout") {

    logout();

}
/*****************************************************************
 * MODEL : COMPANY
 *****************************************************************/

class Company extends BaseModel
{

    /**************************************************************
     * Register Company
     **************************************************************/
    /* Create a new company record in the database with the provided details */
    public function create(

        $companyName,
        $address,
        $telephone,
        $email

    ) {

        /* Prepare the SQL statement to insert a new company record */
        $sql =

            "INSERT INTO Companies
        (
            CompanyName,
            Address,
            Telephone,
            Email
        )

        VALUES
        (
            ?,?,?,?
        )";

        /* Prepare the SQL statement using mysqli_prepare to prevent SQL injection */
        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            "ssss",

            $companyName,

            $address,

            $telephone,

            $email

        );

        /* Execute the prepared statement and return the inserted ID if successful, or false if not */
        if (mysqli_stmt_execute($stmt)) {

            return mysqli_insert_id($this->db);

        }

        return false;

    }

    /**************************************************************
     * Get Company By ID
     **************************************************************/

    /* Retrieve a company record from the database by its CompanyID */
    public function getByID($companyID)
    {

        /* Prepare the SQL statement to select a company record by CompanyID */
        $sql =

            "SELECT *

        FROM Companies

        WHERE CompanyID=?";

        /* Prepare the SQL statement using mysqli_prepare to prevent SQL injection */
        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            "i",

            $companyID

        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);

    }

    /**************************************************************
     * Get All Companies
     **************************************************************/

    /* Retrieve all company records from the database, ordered by CompanyName */
    public function getAll()
    {
        /* Retrieve all company records from the database, ordered by CompanyName */
        $sql =

            "SELECT *

        FROM Companies

        ORDER BY CompanyName";

        return mysqli_query(

            $this->db,

            $sql

        );

    }

    /**************************************************************
     * Update Company
     **************************************************************/

    /* Update an existing company record in the database with the provided details */
    public function update(

        $companyID,

        $companyName,

        $address,

        $telephone,

        $email

    ) {

        /* Prepare the SQL statement to update a company record by CompanyID */
        $sql =

            "UPDATE Companies

        SET

        CompanyName=?,

        Address=?,

        Telephone=?,

        Email=?

        WHERE

        CompanyID=?";

        $stmt = mysqli_prepare(

            $this->db,

            $sql

        );

        /* Bind the parameters to the prepared statement to prevent SQL injection */
        mysqli_stmt_bind_param(

            $stmt,

            "ssssi",

            $companyName,

            $address,

            $telephone,

            $email,

            $companyID

        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Delete Company
     **************************************************************/

    /* Delete a company record from the database by its CompanyID, along with associated users and employees */
    public function delete($companyID)
    {
        /* Begin a transaction to ensure that all deletions are performed atomically */
        mysqli_begin_transaction($this->db);

        /* Use a try-catch block to handle any exceptions that may occur during the deletion process */
        try {

            // Delete Users

            $stmt = mysqli_prepare(

                $this->db,

                "DELETE FROM Users
                 WHERE CompanyID=?"

            );

            mysqli_stmt_bind_param(

                $stmt,

                "i",

                $companyID

            );

            mysqli_stmt_execute($stmt);

            // Delete Employees

            $stmt = mysqli_prepare(

                $this->db,

                "DELETE FROM Employees
                 WHERE CompanyID=?"

            );

            mysqli_stmt_bind_param(

                $stmt,

                "i",

                $companyID

            );

            mysqli_stmt_execute($stmt);

            // Delete Company

            $stmt = mysqli_prepare(

                $this->db,

                "DELETE FROM Companies
                 WHERE CompanyID=?"

            );

            mysqli_stmt_bind_param(

                $stmt,

                "i",

                $companyID

            );

            mysqli_stmt_execute($stmt);

            mysqli_commit($this->db);

            return true;

        } catch (Exception $e) {

            mysqli_rollback($this->db);

            return false;

        }

    }

    /**************************************************************
     * Count Companies
     **************************************************************/

    /* Count the total number of companies in the database */
    public function totalCompanies()
    {
        /* Prepare the SQL statement to count the total number of companies */
        $sql =

            "SELECT COUNT(*) AS Total

        FROM Companies";

        $result = mysqli_query(

            $this->db,

            $sql

        );

        $row = mysqli_fetch_assoc($result);

        return $row["Total"];

    }

}

/*****************************************************************
 * MODEL : USER
 *****************************************************************/

/* User model class for managing user accounts and authentication */
class User extends BaseModel
{

    /**************************************************************
     * Create User
     **************************************************************/
    /* Create a new user account in the database with the provided details */
    public function create(
        $companyID,
        $username,
        $password,
        $role = "COMPANY"
    ) {

        // Hash Password
        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Users
                (CompanyID, Username, Password, Role)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $companyID,
            $username,
            $password,
            $role
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Login
     **************************************************************/

    /* Authenticate a user by verifying the provided username and password against the database */
    public function login($username, $password)
    {
        /* Prepare the SQL statement to select a user record by username and password */
        $sql = "SELECT *
            FROM Users
            WHERE Username=?
            AND Password=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $username,
            $password
        );

        /* Execute the prepared statement and retrieve the result */
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $_SESSION["user"] = mysqli_fetch_assoc($result);
            return true;
        }

        return false;

    }

    /**************************************************************
     * Get User
     **************************************************************/

    /* Retrieve a user record from the database by its UserID */
    public function getUser($userID)
    {
        /* Prepare the SQL statement to select a user record by UserID */
        $sql = "SELECT *
                FROM Users
                WHERE UserID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);

    }

    /**************************************************************
     * Get By Username
     **************************************************************/

    /* Retrieve a user record from the database by its username */
    public function getByUsername($username)
    {
        /* Prepare the SQL statement to select a user record by username */
        $sql = "SELECT *
                FROM Users
                WHERE Username=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $username
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);

    }

    /**************************************************************
     * Username Exists
     **************************************************************/

    /* Check if a username already exists in the database */
    public function usernameExists($username)
    {

        /* Prepare the SQL statement to check if a username exists in the Users table */
        $sql = "SELECT UserID
                FROM Users
                WHERE Username=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $username
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_num_rows($result) > 0;

    }

    /**************************************************************
     * Get Company User
     **************************************************************/

    /* Retrieve a user record associated with a specific company by its CompanyID */
    public function getCompanyUser($companyID)
    {
        /* Prepare the SQL statement to select a user record by CompanyID */
        $sql = "SELECT *
                FROM Users
                WHERE CompanyID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $companyID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);

    }

    /**************************************************************
     * Change Password
     **************************************************************/

    /* Change the password for a user by updating the Password field in the database */
    public function changePassword($userID, $password)
    {
        /* Hash the new password before storing it in the database */
        $password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "UPDATE Users
                SET Password=?
                WHERE UserID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $password,
            $userID
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Delete User
     **************************************************************/
    public function delete($userID)
    {
        /* Delete a user record from the database by its UserID */
        $stmt = mysqli_prepare(
            $this->db,
            "DELETE FROM Users
             WHERE UserID=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userID
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Total Users
     **************************************************************/
    public function totalUsers()
    {
        /* Count the total number of users in the database */
        $result = mysqli_query(
            $this->db,
            "SELECT COUNT(*) AS Total
             FROM Users"
        );

        $row = mysqli_fetch_assoc($result);

        return $row["Total"];

    }

}
/*****************************************************************
 * MODEL : EMPLOYEE
 *****************************************************************/

class Employee extends BaseModel
{

    /**************************************************************
     * Add Employee
     **************************************************************/

    /* Create a new employee record in the database with the provided details */
    public function create(
        $companyID,
        $firstName,
        $lastName,
        $email,
        $department
    ) {
        /* Prepare the SQL statement to insert a new employee record */
        $sql = "INSERT INTO Employees
                (
                    CompanyID,
                    FirstName,
                    LastName,
                    Email,
                    Department
                )
                VALUES
                (?,?,?,?,?)";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "issss",
            $companyID,
            $firstName,
            $lastName,
            $email,
            $department
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Update Employee
     **************************************************************/

    /* Update an existing employee record in the database with the provided details */
    public function update(
        $employeeID,
        $firstName,
        $lastName,
        $email,
        $department
    ) {
        /* Prepare the SQL statement to update an employee record by EmployeeID */
        $sql = "UPDATE Employees

                SET

                FirstName=?,
                LastName=?,
                Email=?,
                Department=?

                WHERE EmployeeID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $firstName,
            $lastName,
            $email,
            $department,
            $employeeID
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Delete Employee
     **************************************************************/

    /* Delete an employee record from the database by its EmployeeID, along with associated registrations */
    public function delete($employeeID)
    {
        /* Begin a transaction to ensure that all deletions are performed atomically */
        mysqli_begin_transaction($this->db);

        try {

            /* Delete registrations first */

            $stmt = mysqli_prepare(
                $this->db,
                "DELETE FROM Registrations
                 WHERE EmployeeID=?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $employeeID
            );

            mysqli_stmt_execute($stmt);

            /* Delete employee */

            $stmt = mysqli_prepare(
                $this->db,
                "DELETE FROM Employees
                 WHERE EmployeeID=?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $employeeID
            );

            mysqli_stmt_execute($stmt);

            mysqli_commit($this->db);

            return true;

        } catch (Exception $e) {

            mysqli_rollback($this->db);

            return false;

        }

    }

    /**************************************************************
     * Get Employee
     **************************************************************/
    public function getByID($employeeID)
    {
        /* Retrieve an employee record from the database by its EmployeeID */
        $sql = "SELECT *

                FROM Employees

                WHERE EmployeeID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        /*  Bind the EmployeeID parameter to the prepared statement to prevent SQL injection */
        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $employeeID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);

    }

    /**************************************************************
     * Get Employees By Company
     **************************************************************/

    /* Retrieve all employee records associated with a specific company by its CompanyID */
    public function getByCompany($companyID)
    {

        /* Prepare the SQL statement to select employee records by CompanyID, ordered by LastName and FirstName */
        $sql = "SELECT *

                FROM Employees

                WHERE CompanyID=?

                ORDER BY LastName, FirstName";

        $stmt = mysqli_prepare($this->db, $sql);

        /* Bind the CompanyID parameter to the prepared statement to prevent SQL injection */
        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $companyID
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);

    }

    /**************************************************************
     * Get All Employees
     **************************************************************/

    /* Retrieve all employee records from the database, along with their associated company names, ordered by CompanyName, LastName, and FirstName */
    public function getAll()
    {
        /* Prepare the SQL statement to select all employee records along with their associated company names */
        $sql = "SELECT

                    Employees.*,

                    Companies.CompanyName

                FROM Employees

                INNER JOIN Companies

                ON Employees.CompanyID =
                Companies.CompanyID

                ORDER BY

                CompanyName,

                LastName,

                FirstName";

        return mysqli_query(
            $this->db,
            $sql
        );

    }

    /**************************************************************
     * Search Employees
     **************************************************************/

    /* Search for employees based on a keyword that matches their first name, last name, or department */
    public function search($keyword)
    {
        /* Prepare the keyword for a SQL LIKE query by adding wildcard characters */
        $keyword = "%" . $keyword . "%";

        /* Prepare the SQL statement to search for employees based on the provided keyword */
        $sql = "SELECT

                    Employees.*,

                    Companies.CompanyName

                FROM Employees

                INNER JOIN Companies

                ON Employees.CompanyID =
                Companies.CompanyID

                WHERE

                FirstName LIKE ?

                OR

                LastName LIKE ?

                OR

                Department LIKE ?

                ORDER BY

                LastName";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $keyword,
            $keyword,
            $keyword
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);

    }

    /**************************************************************
     * Count Employees
     **************************************************************/

    /* Count the total number of employees in the database */
    public function totalEmployees()
    {
        /* Prepare the SQL statement to count the total number of employees */
        $sql = "SELECT COUNT(*) AS Total

                FROM Employees";

        $result = mysqli_query(
            $this->db,
            $sql
        );

        $row = mysqli_fetch_assoc($result);

        return $row["Total"];

    }

    /**************************************************************
     * Count Employees Per Company
     **************************************************************/

    /* Count the total number of employees associated with a specific company by its CompanyID */
    public function totalByCompany($companyID)
    {

        /* Prepare the SQL statement to count the total number of employees for a specific company */
        $sql = "SELECT COUNT(*) AS Total

                FROM Employees

                WHERE CompanyID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $companyID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $row = mysqli_fetch_assoc($result);

        return $row["Total"];

    }

}

/*****************************************************************
 * MODEL : TRAINING
 *****************************************************************/

class Training extends BaseModel
{

    /**************************************************************
     * Create Training
     **************************************************************/

    /* Create a new training record in the database with the provided details */
    public function create(
        $trainingName,
        $cost,
        $duration
    ) {
        /* Prepare the SQL statement to insert a new training record */
        $sql = "INSERT INTO Training
                (
                    TrainingName,
                    Cost,
                    Duration
                )
                VALUES
                (?,?,?)";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sds",
            $trainingName,
            $cost,
            $duration
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Update Training
     **************************************************************/

    /* Update an existing training record in the database with the provided details */
    public function update(
        $trainingID,
        $trainingName,
        $cost,
        $duration
    ) {
        /* Prepare the SQL statement to update a training record by TrainingID */
        $sql = "UPDATE Training

                SET

                TrainingName=?,
                Cost=?,
                Duration=?

                WHERE TrainingID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sdsi",
            $trainingName,
            $cost,
            $duration,
            $trainingID
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Delete Training
     **************************************************************/

    /* Delete a training record from the database by its TrainingID, along with associated registrations */
    public function delete($trainingID)
    {

        mysqli_begin_transaction($this->db);

        try {

            // Delete registrations first

            $stmt = mysqli_prepare(
                $this->db,
                "DELETE FROM Registrations
                 WHERE TrainingID=?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $trainingID
            );

            mysqli_stmt_execute($stmt);

            // Delete training course

            $stmt = mysqli_prepare(
                $this->db,
                "DELETE FROM Training
                 WHERE TrainingID=?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $trainingID
            );

            mysqli_stmt_execute($stmt);

            mysqli_commit($this->db);

            return true;

        } catch (Exception $e) {

            mysqli_rollback($this->db);

            return false;

        }

    }

    /**************************************************************
     * Get Training By ID
     **************************************************************/

    /* Retrieve a training record from the database by its TrainingID */
    public function getByID($trainingID)
    {
        /* Prepare the SQL statement to select a training record by TrainingID */
        $sql = "SELECT *

                FROM Training

                WHERE TrainingID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $trainingID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);

    }

    /**************************************************************
     * Get All Training
     **************************************************************/

    /* Retrieve all training records from the database, ordered by TrainingName */
    public function getAll()
    {
        /* Prepare the SQL statement to select all training records, ordered by TrainingName */
        $sql = "SELECT *

                FROM Training

                ORDER BY TrainingName";

        return mysqli_query(
            $this->db,
            $sql
        );

    }

    /**************************************************************
     * Count Training Courses
     **************************************************************/

    /*  Count the total number of training courses in the database */
    public function totalTraining()
    {
        /* Prepare the SQL statement to count the total number of training courses */
        $result = mysqli_query(
            $this->db,
            "SELECT COUNT(*) AS Total
             FROM Training"
        );

        $row = mysqli_fetch_assoc($result);

        return $row["Total"];

    }

    /**************************************************************
     * Search Training
     **************************************************************/

    /* Search for training courses based on a keyword that matches their TrainingName */
    public function search($keyword)
    {
        /* Prepare the keyword for a SQL LIKE query by adding wildcard characters */
        $keyword = "%" . $keyword . "%";

        $sql = "SELECT *

                FROM Training

                WHERE TrainingName LIKE ?

                ORDER BY TrainingName";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $keyword
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);

    }

    /**************************************************************
     * Get Training Cost
     **************************************************************/

    /* Retrieve the cost of a specific training course by its TrainingID */
    public function getCost($trainingID)
    {
        /* Prepare the SQL statement to select the Cost of a training course by TrainingID */
        $sql = "SELECT Cost
                FROM Training
                WHERE TrainingID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $trainingID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $row = mysqli_fetch_assoc($result);

        return $row["Cost"];

    }

}

/*****************************************************************
 * MODEL : REGISTRATION
 *****************************************************************/

class Registration extends BaseModel
{

    /**************************************************************
     * Register Employee For Training
     **************************************************************/

    /* Create a new registration record in the database for an employee attending a training course, preventing duplicate registrations */
    public function create(
        $employeeID,
        $trainingID,
        $registrationDate
    ) {

        // Prevent duplicate registrations
        $check = mysqli_prepare(
            $this->db,
            "SELECT RegistrationID
             FROM Registrations
             WHERE EmployeeID=? AND TrainingID=?"
        );

        mysqli_stmt_bind_param(
            $check,
            "ii",
            $employeeID,
            $trainingID
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {
            return false;
        }

        $sql = "INSERT INTO Registrations
                (
                    EmployeeID,
                    TrainingID,
                    RegistrationDate
                )
                VALUES
                (?,?,?)";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iis",
            $employeeID,
            $trainingID,
            $registrationDate
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Update Registration
     **************************************************************/

    /* Update an existing registration record in the database with the provided details */
    public function update(
        $registrationID,
        $trainingID,
        $registrationDate
    ) {

        $sql = "UPDATE Registrations

                SET

                TrainingID=?,
                RegistrationDate=?

                WHERE RegistrationID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "isi",
            $trainingID,
            $registrationDate,
            $registrationID
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Delete Registration
     **************************************************************/

    /* Delete a registration record from the database by its RegistrationID */
    public function delete($registrationID)
    {

        $stmt = mysqli_prepare(
            $this->db,
            "DELETE FROM Registrations
             WHERE RegistrationID=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $registrationID
        );

        return mysqli_stmt_execute($stmt);

    }

    /**************************************************************
     * Get Registration By ID
     **************************************************************/

    /* Retrieve a registration record from the database by its RegistrationID */
    public function getByID($registrationID)
    {

        $stmt = mysqli_prepare(
            $this->db,
            "SELECT *
             FROM Registrations
             WHERE RegistrationID=?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $registrationID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($result);

    }

    /**************************************************************
     * Company Registrations
     **************************************************************/

    /* Retrieve all registration records associated with a specific company by its CompanyID, including employee and training details */
    public function getCompanyRegistrations($companyID)
    {
        /* Prepare the SQL statement to select registration records for a specific company, including employee and training details, ordered by RegistrationDate in descending order */
        $sql = "SELECT

                    Registrations.*,

                    Employees.FirstName,

                    Employees.LastName,

                    Training.TrainingName,

                    Training.Cost,

                    Training.Duration

                FROM Registrations

                INNER JOIN Employees
                ON Registrations.EmployeeID =
                Employees.EmployeeID

                INNER JOIN Training
                ON Registrations.TrainingID =
                Training.TrainingID

                WHERE Employees.CompanyID=?

                ORDER BY RegistrationDate DESC";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $companyID
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);

    }

    /**************************************************************
     * Participants By Training
     **************************************************************/

    /* Retrieve all participants (employees) registered for a specific training course by its TrainingID, including employee and company details, ordered by CompanyName and LastName */
    public function participantsByTraining($trainingID)
    {
        /* Prepare the SQL statement to select participants for a specific training course, including employee and company details, ordered by CompanyName and LastName */
        $sql = "SELECT

                    Employees.FirstName,

                    Employees.LastName,

                    Companies.CompanyName,

                    Registrations.RegistrationDate

                FROM Registrations

                INNER JOIN Employees
                ON Registrations.EmployeeID =
                Employees.EmployeeID

                INNER JOIN Companies
                ON Employees.CompanyID =
                Companies.CompanyID

                WHERE TrainingID=?

                ORDER BY Companies.CompanyName,
                         Employees.LastName";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $trainingID
        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);

    }

    /**************************************************************
     * Total Company Training Cost
     **************************************************************/

    /* Calculate the total cost of training for a specific company by summing the costs of all training courses attended by its employees */
    public function companyTrainingCost($companyID)
    {
        /* Prepare the SQL statement to calculate the total cost of training for a specific company by summing the costs of all training courses attended by its employees */
        $sql = "SELECT

                    SUM(Training.Cost) AS TotalCost

                FROM Registrations

                INNER JOIN Employees
                ON Registrations.EmployeeID =
                Employees.EmployeeID

                INNER JOIN Training
                ON Registrations.TrainingID =
                Training.TrainingID

                WHERE Employees.CompanyID=?";

        $stmt = mysqli_prepare($this->db, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $companyID
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $row = mysqli_fetch_assoc($result);

        return $row["TotalCost"] ?? 0;

    }

    /**************************************************************
     * Total Registrations
     **************************************************************/

    /* Count the total number of registrations in the database */
    public function totalRegistrations()
    {

        $result = mysqli_query(
            $this->db,
            "SELECT COUNT(*) AS Total
             FROM Registrations"
        );

        $row = mysqli_fetch_assoc($result);

        return $row["Total"];

    }

    /**************************************************************
     * Training Summary Report
     **************************************************************/

    /* Generate a summary report of training courses, including the total number of participants and total revenue for each course */
    public function trainingSummary()
    {
        /* Prepare the SQL statement to generate a summary report of training courses, including the total number of participants and total revenue for each course */
        $sql = "SELECT

                    Training.TrainingName,

                    COUNT(Registrations.RegistrationID)
                    AS TotalParticipants,

                    SUM(Training.Cost)
                    AS TotalRevenue

                FROM Training

                LEFT JOIN Registrations

                ON Training.TrainingID =
                Registrations.TrainingID

                GROUP BY

                Training.TrainingID

                ORDER BY

                Training.TrainingName";

        return mysqli_query(
            $this->db,
            $sql
        );

    }

}
/*****************************************************************
 * CONTROLLER : AUTHENTICATION
 *****************************************************************/


class AuthController
{
    /* Controller class for handling user authentication, including login, logout, and access control */
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**************************************************************
     * Login
     **************************************************************/

    /* Handle user login by validating credentials and redirecting based on user role */
    public function login()
    {
        /* Check if the login form has been submitted */

        if (!isset($_POST["login"])) {
            return;
        }
        /* Clean and retrieve the username and password from the POST request */
        $username = clean($_POST["username"]);
        $password = $_POST["password"];

        if ($username == "" || $password == "") {
            setFlash(
                "Please enter both username and password.",
                "error"
            );
            return;
        }
        /* Attempt to log in the user using the User model's login method */
        if ($this->userModel->login($username, $password)) {

            if ($_SESSION["user"]["Role"] == "ADMIN") {
                redirect("admin");
            } else {
                redirect("dashboard");
            }

        } else {

            setFlash(
                "Invalid username or password.",
                "error"
            );

        }

    }

    /**************************************************************
     * Logout
     **************************************************************/
    /* Handle user logout by clearing the session and redirecting to the login page */
    public function logout()
    {

        $_SESSION = array();

        session_destroy();

        redirect("login");

    }

    /**************************************************************
     * Require Login
     **************************************************************/

    /* Ensure that the user is logged in before accessing certain pages, redirecting to the login page if not authenticated */
    public function requireLogin()
    {

        if (!loggedIn()) {
            redirect("login");
        }

    }

    /**************************************************************
     * Require Admin
     **************************************************************/

    /* Ensure that the user has administrator privileges before accessing certain pages, redirecting to the login page if not authorized */
    public function requireAdmin()
    {
        /* Check if the user is logged in and has the "ADMIN" role, redirecting to the login page with an error message if not authorized */
        if (!adminLoggedIn()) {

            setFlash(
                "Administrator access required.",
                "error"
            );

            redirect("login");

        }

    }

    /**************************************************************
     * Require Company
     **************************************************************/

    /* Ensure that the user is logged in as a company before accessing certain pages, redirecting to the login page if not authorized */
    public function requireCompany()
    {
        /* Check if the user is logged in and has the "COMPANY" role, redirecting to the login page with an error message if not authorized */
        if (!companyLoggedIn()) {

            setFlash(
                "Company login required.",
                "error"
            );

            redirect("login");

        }

    }

}
/*****************************************************************
 * CONTROLLER : COMPANY
 *****************************************************************/

/*
 * Controller class for handling company-related actions, including registration and deletion of companies.
 */
class CompanyController
{

    private $companyModel;

    private $userModel;

    public function __construct()
    {

        $this->companyModel = new Company();

        $this->userModel = new User();

    }

    /**************************************************************
     * Register Company
     **************************************************************/

    /* Handle company registration by validating input, checking for existing usernames, creating a new company and user account, and redirecting to the login page upon successful registration */
    public function register()
    {
        /* Check if the company registration form has been submitted */
        if (!isset($_POST["registerCompany"])) {
            return;
        }

        /* Clean and retrieve the company details and user credentials from the POST request */
        $companyName = clean($_POST["companyName"]);
        $address = clean($_POST["address"]);
        $telephone = clean($_POST["telephone"]);
        $email = clean($_POST["email"]);

        /* Clean and retrieve the username and password from the POST request */
        $username = clean($_POST["username"]);
        $password = $_POST["password"];

        /*  Validate that all required fields are filled in, and set an error message if any field is empty */
        if (
            $companyName == "" ||
            $username == "" ||
            $password == ""
        ) {

            setFlash(
                "Please complete all required fields.",
                "error"
            );

            return;

        }

        /* Check if the provided username already exists in the database, and set an error message if it does */
        if ($this->userModel->usernameExists($username)) {

            setFlash(
                "Username already exists.",
                "error"
            );

            return;

        }

        $companyID = $this->companyModel->create(
            $companyName,
            $address,
            $telephone,
            $email
        );

        /* If the company was successfully created, create a corresponding user account for the company with the provided username and password, and set a success message. If the company creation failed, set an error message. Finally, redirect to the login page. */
        if ($companyID) {

            $this->userModel->create(
                $companyID,
                $username,
                $password,
                "COMPANY"
            );

            setFlash(
                "Company registered successfully."
            );

            redirect("login");

        } else {

            setFlash(
                "Registration failed.",
                "error"
            );

        }

    }

    /**************************************************************
     * Delete Company
     **************************************************************/

    /* Handle the deletion of a company by its CompanyID, along with associated employees, training courses, and registrations, and redirect to the admin page with a success message */
    public function delete()
    {

        if (isset($_GET["deleteCompany"])) {

            $this->companyModel->delete(

                intval($_GET["deleteCompany"])

            );

            setFlash(
                "Company deleted."
            );

            redirect("admin");

        }

    }

}
/*****************************************************************
 * CONTROLLER : EMPLOYEE
 *****************************************************************/

/* Controller class for handling employee-related actions, including creation, updating, and deletion of employee records. */

class EmployeeController
{

    private $employeeModel;

    /* Constructor method to initialize the EmployeeController class and create an instance of the Employee model for handling employee-related database operations. */
    public function __construct()
    {
        $this->employeeModel = new Employee();
    }

    /**************************************************************
     * Add Employee
     **************************************************************/

    /* Handle the creation of a new employee record by validating input, creating the employee in the database, and redirecting to the employees page with a success message. */
    public function create()
    {

        /* Check if the employee creation form has been submitted, and return early if not. */
        if (!isset($_POST["addEmployee"])) {
            return;
        }

        $companyID = currentCompanyID();

        $firstName = clean($_POST["firstName"]);
        $lastName = clean($_POST["lastName"]);
        $email = clean($_POST["email"]);
        $department = clean($_POST["department"]);

        /* Validate that all required fields are filled in, and set an error message if any field is empty */
        if (
            $firstName == "" ||
            $lastName == "" ||
            $email == ""
        ) {
            setFlash("Please complete all required fields.", "error");
            return;
        }

        /* Attempt to create the employee record in the database using the Employee model's create method, and set an error message if the creation fails */
        if (
            !$this->employeeModel->create(
                $companyID,
                $firstName,
                $lastName,
                $email,
                $department
            )
        ) {
            setFlash("Unable to add employee.", "error");
            return;
        }

        setFlash("Employee added successfully.");
        redirect("employees");

    }

    /**************************************************************
     * Update Employee
     **************************************************************/

    /* Handle the updating of an existing employee record by validating input, updating the employee in the database, and redirecting to the employees page with a success message. */
    public function update()
    {
        /* Check if the employee update form has been submitted, and return early if not. */
        if (!isset($_POST["updateEmployee"])) {
            return;
        }

        /* Clean and retrieve the employee details from the POST request */
        $this->employeeModel->update(

            intval($_POST["employeeID"]),

            clean($_POST["firstName"]),

            clean($_POST["lastName"]),

            clean($_POST["email"]),

            clean($_POST["department"])

        );

        setFlash("Employee updated.");

        redirect("employees");

    }

    /**************************************************************
     * Delete Employee
     **************************************************************/

    /* Handle the deletion of an employee record by its EmployeeID, and redirect to the employees page with a success message. */
    public function delete()
    {

        if (isset($_GET["deleteEmployee"])) {

            $this->employeeModel->delete(

                intval($_GET["deleteEmployee"])

            );

            setFlash("Employee deleted.");

            redirect("employees");

        }

    }

}

/*****************************************************************
 * CONTROLLER : TRAINING
 *****************************************************************/

/* Controller class for handling training-related actions, including creation, updating, and deletion of training records. */

class TrainingController
{

    private $trainingModel;

    public function __construct()
    {
        $this->trainingModel = new Training();
    }

    /* Handle the creation of a new training record by validating input, creating the training in the database, and redirecting to the training page with a success message. */
    public function create()
    {

        if (!isset($_POST["addTraining"])) {
            return;
        }

        $this->trainingModel->create(

            clean($_POST["trainingName"]),

            $_POST["cost"],

            clean($_POST["duration"])

        );

        setFlash("Training course added.");

        redirect("training");

    }

    /* Handle the updating of an existing training record by validating input, updating the training in the database, and redirecting to the training page with a success message. */
    public function update()
    {

        if (!isset($_POST["updateTraining"])) {
            return;
        }

        $this->trainingModel->update(

            intval($_POST["trainingID"]),

            clean($_POST["trainingName"]),

            $_POST["cost"],

            clean($_POST["duration"])

        );

        setFlash("Training updated.");

        redirect("training");

    }

    /* Handle the deletion of a training record by its TrainingID, and redirect to the training page with a success message. */
    public function delete()
    {

        if (isset($_GET["deleteTraining"])) {

            $this->trainingModel->delete(

                intval($_GET["deleteTraining"])

            );

            setFlash("Training deleted.");

            redirect("training");

        }

    }

}
/*****************************************************************
 * CONTROLLER : REGISTRATION
 *****************************************************************/

/* Controller class for handling registration-related actions, including creating, updating, and deleting employee registrations for training courses. */
class RegistrationController
{
    /* Constructor method to initialize the RegistrationController class and create an instance of the Registration model for handling registration-related database operations. */
    private $registrationModel;

    public function __construct()
    {

        $this->registrationModel = new Registration();

    }

    /* Handle the creation of a new employee registration for a training course by validating input, creating the registration in the database, and redirecting to the registrations page with a success message. */
    public function create()
    {

        if (!isset($_POST["registerTraining"])) {
            return;
        }

        /* Clean and retrieve the employee ID, training ID, and registration date from the POST request */
        if (

            $this->registrationModel->create(

                intval($_POST["employeeID"]),

                intval($_POST["trainingID"]),

                $_POST["registrationDate"]

            )

        ) {

            setFlash(

                "Training registration successful."

            );

        } else {

            setFlash(

                "Employee is already registered for this training.",

                "error"

            );

        }

        redirect("registrations");

    }

    /* Handle the updating of an existing employee registration for a training course by validating input, updating the registration in the database, and redirecting to the registrations page with a success message. */
    public function update()
    {

        /* Check if the registration update form has been submitted, and return early if not. */
        if (!isset($_POST["updateRegistration"])) {
            return;
        }

        $this->registrationModel->update(

            intval($_POST["registrationID"]),

            intval($_POST["trainingID"]),

            $_POST["registrationDate"]

        );

        setFlash("Registration updated.");

        redirect("registrations");

    }

    /* Handle the deletion of an employee registration for a training course by its RegistrationID, and redirect to the registrations page with a success message. */
    public function delete()
    {

        if (isset($_GET["deleteRegistration"])) {

            $this->registrationModel->delete(

                intval($_GET["deleteRegistration"])

            );

            setFlash("Registration deleted.");

            redirect("registrations");

        }

    }

}
/*****************************************************************
 * CONTROLLER : ADMIN
 *****************************************************************/

/* Controller class for handling administrative actions, including retrieving dashboard statistics and generating training summary reports. */

class AdminController
{

    private $companyModel;
    private $employeeModel;
    private $trainingModel;
    private $registrationModel;
    private $userModel;

    /**************************************************************
     * Constructor
     **************************************************************/
    public function __construct()
    {

        $this->companyModel = new Company();
        $this->employeeModel = new Employee();
        $this->trainingModel = new Training();
        $this->registrationModel = new Registration();
        $this->userModel = new User();

    }

    /**************************************************************
     * Dashboard Statistics
     **************************************************************/

    public function dashboard()
    {

        return array(

            "companies" => $this->companyModel->totalCompanies(),

            "employees" => $this->employeeModel->totalEmployees(),

            "training" => $this->trainingModel->totalTraining(),

            "registrations" => $this->registrationModel->totalRegistrations(),

            "users" => $this->userModel->totalUsers()

        );

    }

    /**************************************************************
     * Training Summary
     **************************************************************/

    public function trainingSummary()
    {

        return $this->registrationModel->trainingSummary();

    }

}

/*****************************************************************
 * PART 4 - FRONT CONTROLLER (ROUTER)
 *****************************************************************/

/*
|--------------------------------------------------------------------------
| Instantiate Controllers
|--------------------------------------------------------------------------
*/

$authController = new AuthController();
$companyController = new CompanyController();
$employeeController = new EmployeeController();
$trainingController = new TrainingController();
$registrationController = new RegistrationController();
$adminController = new AdminController();

/*
|--------------------------------------------------------------------------
| Handle POST Requests
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Authentication
    $authController->login();

    // Company
    $companyController->register();

    // Employees
    $employeeController->create();
    $employeeController->update();

    // Training
    $trainingController->create();
    $trainingController->update();

    // Registrations
    $registrationController->create();
    $registrationController->update();

}

/*
|--------------------------------------------------------------------------
| Handle GET Actions
|--------------------------------------------------------------------------
*/
/* Handle specific actions based on the "action" parameter in the GET request, such as logging out, deleting companies, employees, training courses, or registrations. */
if (isset($_GET["action"])) {

    switch ($_GET["action"]) {

        case "logout":

            $authController->logout();

            break;

        case "deleteCompany":

            $companyController->delete();

            break;

        case "deleteEmployee":

            $employeeController->delete();

            break;

        case "deleteTraining":

            $trainingController->delete();

            break;

        case "deleteRegistration":

            $registrationController->delete();

            break;

    }

}

/*
|--------------------------------------------------------------------------
| Determine Current Page
|--------------------------------------------------------------------------
*/

$page = "home";

if (isset($_GET["page"])) {

    $page = clean($_GET["page"]);

}

/*
|--------------------------------------------------------------------------
| Load Data Required By Views
|--------------------------------------------------------------------------
*/

$companies = null;
$employees = null;
$training = null;
$registrations = null;
$dashboardStats = null;

/* Load data based on the current page, such as company details, employee lists, training courses, registrations, and dashboard statistics for admin users. */
switch ($page) {
    /* Handle the "dashboard" page by checking if a company is logged in, and if so, retrieve the list of employees and registrations associated with the current company. */
    case "dashboard":

        if (companyLoggedIn()) {

            $employeeModel = new Employee();
            $registrationModel = new Registration();

            $employees = $employeeModel->getByCompany(
                currentCompanyID()
            );

            $registrations = $registrationModel->getCompanyRegistrations(
                currentCompanyID()
            );

        }

        break;

    case "employees":

        if (companyLoggedIn()) {

            $employeeModel = new Employee();

            $employees = $employeeModel->getByCompany(
                currentCompanyID()
            );

        }

        break;

    case "training":

        $trainingModel = new Training();

        $training = $trainingModel->getAll();

        break;

    case "admin":

        if (adminLoggedIn()) {

            $companyModel = new Company();
            $employeeModel = new Employee();
            $trainingModel = new Training();

            $companies = $companyModel->getAll();
            $employees = $employeeModel->getAll();
            $training = $trainingModel->getAll();

            $dashboardStats = $adminController->dashboard();

        }

        break;

    case "reports":

        if (adminLoggedIn()) {

            $registrationModel = new Registration();

            $registrations = $registrationModel->trainingSummary();

        }

        break;

}

/*****************************************************************
 * END OF ROUTER
 *
 * HTML starts AFTER this point.
 *****************************************************************/

/*****************************************************************
 * PART 5 - VIEW LAYER
 *****************************************************************/
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Employee Training Management System</title>

    <style>
        /* Reset and basic styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styles for background and font */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
        }

        /* Container for centering content and setting max width */
        .container {
            width: 95%;
            max-width: 1200px;
            margin: 20px auto;
        }

        /* Card styles for sections with padding, border radius, and shadow */
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }

        /* Heading styles for h1 and h2 with specific colors and margins */
        h1 {
            color: #40e90d;
            margin-bottom: 15px;
        }

        h2 {
            color: #1fe90d;
            margin-bottom: 15px;
        }

        /* Table styles for width, border collapse, and cell padding */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #40e90d;
            color: white;
            padding: 10px;
        }

        table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        /* Form styles for inputs, selects, and buttons with padding and colors */
        input,
        select {
            width: 100%;
            padding: 10px;
            margin: 6px 0 15px;
        }

        button,
        input[type=submit] {

            background: #40e90d;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;

        }

        button:hover,
        input[type=submit]:hover {

            background: #1fe90d;

        }

        /* Style for links in the menu */
        .message {

            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;

        }

        /* Style for success and error messages */
        .success {

            background: #d4edda;
            color: #155724;

        }

        .error {

            background: #f8d7da;
            color: #721c24;

        }

        /* Style for the navigation menu */
        .home-links {

            display: flex;
            gap: 20px;
            margin-top: 20px;

        }

        .home-links a {

            text-decoration: none;
            background: #40e90d;
            color: white;
            padding: 15px 25px;

        }
    </style>

</head>

<body>
    <!-- Include the main menu and page-specific menu -->
    <?php include("menu.inc"); ?>
    <?php include("menu4.inc"); ?>

    <!-- Main container for the content of the page -->
    <div class="container">

        <!-- Display flash messages if any are set -->
        <?php $flash = getFlash();
        if ($flash) {
            echo "<div class='message " .
                $flash["type"] . "'>";
            echo $flash["text"];
            echo "</div>";
        }
        /***************************************************************** * HOME PAGE
         *****************************************************************/
        if (
            $page ==
            "home"
        ) { ?>

            <!-- Display the home page content with a welcome message and links to register or login -->
            <div class="card">

                <h1>Employee Training Management System</h1>

                <p>

                    Welcome to the Employee Training Management System. This system allows companies
                    to:

                </p>

                <br>

                <ul>

                    <li>Register a company</li>

                    <li>Login</li>

                    <li>Manage employees</li>

                    <li>Register employees for training</li>

                    <li>Generate reports</li>

                </ul>

                <!-- Display links to register a company or login -->
                <div class="home-links">

                    <a href="task4.php?page=register">

                        Register Company

                    </a>

                    <a href="task4.php?page=login">

                        Login

                    </a>

                </div>

            </div>

            <!-- Display an image related to employee training management -->
            <?php
        }

        /*****************************************************************
         * LOGIN PAGE
         *****************************************************************/ elseif ($page == "login") {
            ?>

            <div class="card">

                <h2>Login</h2>

                <form method="post">

                    <label>Username</label>

                    <input type="text" name="username" required>

                    <label>Password</label>

                    <input type="password" name="password" required>

                    <input type="submit" name="login" value="Login">

                </form>

            </div>

            <?php
        }

        /*****************************************************************
         * COMPANY REGISTRATION
         *****************************************************************/ elseif ($page == "register") {
            ?>

            <div class="card">

                <h2>Register Company</h2>

                <form method="post">

                    <label>Company Name</label>

                    <input type="text" name="companyName" required>

                    <label>Address</label>

                    <input type="text" name="address">

                    <label>Telephone</label>

                    <input type="text" name="telephone">

                    <label>Email</label>

                    <input type="email" name="email">

                    <hr><br>

                    <h3>Login Details</h3>

                    <label>Username</label>

                    <input type="text" name="username" required>

                    <label>Password</label>

                    <input type="password" name="password" required>

                    <input type="submit" name="registerCompany" value="Register Company">

                </form>

            </div>

        <?php } /***************************************************************** *
VIEW ROUTER *****************************************************************/
        switch ($page) { /************************************************************ *
HOME PAGE ************************************************************/
            case
            "home": ?>


                <?php break; /************************************************************ *
      LOGIN PAGE ************************************************************/
            case
            "login": ?>

                <!-- Display the login form for users to enter their username and password -->


                <?php break; /*****************************************************************
                   * TRAINING REGISTRATION
                   *****************************************************************/
            case
            "registrations":
                $employeeModel = new Employee();
                $trainingModel = new
                    Training();
                $employeeList = $employeeModel->getByCompany(currentCompanyID());
                $trainingList = $trainingModel->getAll(); ?>

                <!-- Display the form for registering an employee for a training course, including dropdowns for selecting an employee and a training course, and a date input for the registration date -->
                <div class="card">

                    <h2>Register Employee For Training</h2>

                    <form method="post">

                        <label>Employee</label>

                        <select name="employeeID" required>

                            <option value="">Select Employee</option>

                            <?php while ($emp = mysqli_fetch_assoc($employeeList)) { ?>

                                <option value="<?php echo $emp[" EmployeeID"]; ?>">

                                    <?php echo $emp["FirstName"] . " " . $emp["LastName"]; ?>

                                </option>

                                <?php

                            }

                            ?>

                        </select>

                        <label>Training Course</label>

                        <!-- Dropdown for selecting a training course, populated with available training courses from the database -->
                        <select name="trainingID" required>

                            <option value="">Select Training</option>

                            <?php while ($course = mysqli_fetch_assoc($trainingList)) { ?>

                                <option value="<?php echo $course[" TrainingID"]; ?>">

                                    <?php echo $course["TrainingName"]; ?>

                                </option>

                                <?php

                            }

                            ?>

                        </select>

                        <!-- Input for selecting the registration date, defaulting to the current date -->
                        <label>Registration Date</label>

                        <input type="date" name="registrationDate" value="<?php echo date('Y-m-d'); ?>" required>

                        <input type="submit" name="registerTraining" value="Register Employee">

                    </form>

                </div>

                <?php $registrationModel = new Registration();
                $registrations =
                    $registrationModel->getCompanyRegistrations(currentCompanyID()); ?>

                <!-- Display the list of training registrations for employees, including details such as employee name, training course, cost, duration, registration date, and an option to delete the registration -->
                <div class="card">

                    <h2>Training Registrations</h2>

                    <!-- Table displaying the list of training registrations for employees, including details such as employee name, training course, cost, duration, registration date, and an option to delete the registration -->
                    <table>

                        <tr>

                            <th>ID</th>

                            <th>Employee</th>

                            <th>Training</th>

                            <th>Cost</th>

                            <th>Duration</th>

                            <th>Date</th>

                            <th>Action</th>

                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($registrations)) { ?>

                            <tr>

                                <td>

                                    <?php echo $row["RegistrationID"]; ?>

                                </td>

                                <td>

                                    <?php echo $row["FirstName"] . " " . $row["LastName"]; ?>

                                </td>

                                <td>

                                    <?php echo $row["TrainingName"]; ?>

                                </td>

                                <td>

                                    R
                                    <?php echo number_format($row["Cost"], 2); ?>

                                </td>

                                <td>

                                    <?php echo $row["Duration"]; ?>

                                </td>

                                <td>

                                    <?php echo $row["RegistrationDate"]; ?>

                                </td>

                                <td>

                                    <a onclick="return confirm('Delete registration?')" href="task4.php?action=deleteRegistration&deleteRegistration=<?php echo $row["
                                RegistrationID"]; ?>">

                                        Delete

                                    </a>

                                </td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <!-- Display a link to go back to the dashboard -->
                <?php

                break;

            case "dashboard":
                ?>

                <div class="card">

                    <h1>Company Dashboard</h1>

                    <p>

                        Welcome

                        <strong>

                            <?php echo $_SESSION["user"]["Username"]; ?>

                        </strong>

                    </p>

                    <br>

                    <?php $employeeModel = new Employee();
                    $totalEmployees =
                        $employeeModel->totalByCompany(currentCompanyID());
                    $registrationModel = new
                        Registration();
                    $totalCost = $registrationModel->companyTrainingCost(
                        currentCompanyID()
                    );
                    $totalRegistrations = mysqli_num_rows(
                        $registrationModel->getCompanyRegistrations(currentCompanyID())
                    ); ?>

                    <table>

                        <tr>

                            <th>Total Employees</th>

                            <th>Training Registrations</th>

                            <th>Total Training Cost</th>

                        </tr>

                        <tr>

                            <td>

                                <?php echo $totalEmployees; ?>

                            </td>

                            <td>

                                <?php echo $totalRegistrations; ?>

                            </td>

                            <td>

                                R
                                <?php echo number_format($totalCost, 2); ?>

                            </td>

                        </tr>

                    </table>

                    <br>

                    <a href="task4.php?page=employees">
                        Manage Employees
                    </a>

                    &nbsp;&nbsp;

                    <a href="task4.php?page=registrations">
                        Training Registrations
                    </a>

                </div>

                <?php
                break;

            case "employees":
                ?>

                <div class="card">

                    <h2>Add Employee</h2>

                    <form method="post">

                        <label>First Name</label>

                        <input type="text" name="firstName" required>

                        <label>Last Name</label>

                        <input type="text" name="lastName" required>

                        <label>Email</label>

                        <input type="email" name="email" required>

                        <label>Department</label>

                        <input type="text" name="department" required>

                        <input type="submit" name="addEmployee" value="Add Employee">

                    </form>

                </div>

                <div class="card">

                    <h2>Employee List</h2>

                    <table>

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Department</th>

                            <th>Actions</th>

                        </tr>

                        <?php if ($employees) {
                            while ($row = mysqli_fetch_assoc($employees)) { ?>

                                <tr>

                                    <td>

                                        <?php echo $row["EmployeeID"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["FirstName"] . " " . $row["LastName"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["Email"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["Department"]; ?>

                                    </td>

                                    <td>

                                        <a href="task4.php?page=editEmployee&id=<?php echo $row[" EmployeeID"]; ?>">

                                            Edit

                                        </a>

                                        |

                                        <a onclick="return confirm('Delete employee?')" href="task4.php?action=deleteEmployee&deleteEmployee=<?php echo $row["
                                EmployeeID"]; ?>">

                                            Delete

                                        </a>

                                    </td>

                                </tr>

                            <?php }
                        } ?>

                    </table>

                </div>

                <?php break; /*****************************************************************
                   * ADMINISTRATOR DASHBOARD
                   *****************************************************************/
            case "admin":
                if (!adminLoggedIn()) {
                    redirect("login");
                } ?>

                <div class="card">

                    <h1>Administrator Dashboard</h1>

                    <?php $stats = $adminController->dashboard(); ?>

                    <table>

                        <tr>

                            <th>Companies</th>
                            <th>Users</th>
                            <th>Employees</th>
                            <th>Training Courses</th>
                            <th>Registrations</th>

                        </tr>

                        <tr>

                            <td><?php echo $stats["companies"]; ?></td>
                            <td><?php echo $stats["users"]; ?></td>
                            <td><?php echo $stats["employees"]; ?></td>
                            <td><?php echo $stats["training"]; ?></td>
                            <td><?php echo $stats["registrations"]; ?></td>

                        </tr>

                    </table>

                </div>

                <?php $companyModel = new Company();
                $companies = $companyModel->getAll(); ?>

                <div class="card">

                    <h2>Registered Companies</h2>

                    <table>

                        <tr>

                            <th>ID</th>
                            <th>Company</th>
                            <th>Telephone</th>
                            <th>Email</th>
                            <th>Action</th>

                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($companies)) { ?>

                            <tr>

                                <td><?php echo $row["CompanyID"]; ?></td>

                                <td><?php echo $row["CompanyName"]; ?></td>

                                <td><?php echo $row["Telephone"]; ?></td>

                                <td><?php echo $row["Email"]; ?></td>

                                <td>

                                    <a onclick="return confirm('Delete Company?')" href="task4.php?action=deleteCompany&deleteCompany=<?php echo $row["
                                CompanyID"]; ?>">

                                        Delete

                                    </a>

                                </td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <?php $employeeModel = new Employee();
                $employees = $employeeModel->getAll(); ?>

                <div class="card">

                    <h2>All Employees</h2>

                    <table>

                        <tr>

                            <th>ID</th>
                            <th>Employee</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Department</th>

                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($employees)) { ?>

                            <tr>

                                <td><?php echo $row["EmployeeID"]; ?></td>

                                <td>

                                    <?php echo $row["FirstName"] . " " . $row["LastName"]; ?>

                                </td>

                                <td><?php echo $row["CompanyName"]; ?></td>

                                <td><?php echo $row["Email"]; ?></td>

                                <td><?php echo $row["Department"]; ?></td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <?php $trainingModel = new Training();
                $training = $trainingModel->getAll(); ?>

                <div class="card">

                    <h2>Training Courses</h2>

                    <table>

                        <tr>

                            <th>ID</th>
                            <th>Training</th>
                            <th>Cost</th>
                            <th>Duration</th>

                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($training)) { ?>

                            <tr>

                                <td><?php echo $row["TrainingID"]; ?></td>

                                <td><?php echo $row["TrainingName"]; ?></td>

                                <td>

                                    R
                                    <?php echo number_format($row["Cost"], 2); ?>

                                </td>

                                <td><?php echo $row["Duration"]; ?></td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <?php $registrationModel = new Registration();
                $summary =
                    $registrationModel->trainingSummary(); ?>

                <div class="card">

                    <h2>Training Summary</h2>

                    <table>

                        <tr>

                            <th>Training</th>
                            <th>Participants</th>
                            <th>Total Revenue</th>

                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($summary)) { ?>

                            <tr>

                                <td>

                                    <?php echo $row["TrainingName"]; ?>

                                </td>

                                <td>

                                    <?php echo $row["TotalParticipants"]; ?>

                                </td>

                                <td>

                                    R
                                    <?php echo number_format($row["TotalRevenue"], 2); ?>

                                </td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <?php break; /*****************************************************************
                   * REPORTS *****************************************************************/
            case "reports":
                if (!adminLoggedIn()) {
                    redirect("login");
                } ?>

                <div class="card">

                    <h1>System Reports</h1>

                </div>

                <?php /*============================================================= REPORT 1
                  ==============================================================*/ $companyModel =
                    new Company();
                $companies = $companyModel->getAll(); ?>

                <div class="card">

                    <h2>Company Training Cost Report</h2>

                    <table>

                        <tr>

                            <th>Company</th>
                            <th>Total Training Cost</th>

                        </tr>

                        <?php $registrationModel = new Registration();
                        while (
                            $company =
                            mysqli_fetch_assoc($companies)
                        ) {
                            $total =
                                $registrationModel->companyTrainingCost($company["CompanyID"]); ?>

                            <tr>

                                <td>

                                    <?php echo $company["CompanyName"]; ?>

                                </td>

                                <td>

                                    R
                                    <?php echo number_format($total, 2); ?>

                                </td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <?php /*============================================================= REPORT 2
                  ==============================================================*/ $companies =
                    $companyModel->getAll();
                $employeeModel = new Employee(); ?>

                <div class="card">

                    <h2>Employees Per Company</h2>

                    <table>

                        <tr>

                            <th>Company</th>
                            <th>Total Employees</th>

                        </tr>

                        <?php while ($company = mysqli_fetch_assoc($companies)) { ?>

                            <tr>

                                <td>

                                    <?php echo $company["CompanyName"]; ?>

                                </td>

                                <td>

                                    <?php echo $employeeModel->totalByCompany($company["CompanyID"]); ?>

                                </td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <?php /*============================================================= REPORT 3
                  ==============================================================*/ $trainingModel
                    = new Training();
                $courses = $trainingModel->getAll(); ?>

                <div class="card">

                    <h2>Training Participants</h2>

                    <?php while ($course = mysqli_fetch_assoc($courses)) { ?>

                        <h3>

                            <?php echo $course["TrainingName"]; ?>

                        </h3>

                        <table>

                            <tr>

                                <th>Employee</th>

                                <th>Company</th>

                                <th>Date</th>

                            </tr>

                            <?php $list = $registrationModel->participantsByTraining(
                                $course["TrainingID"]
                            );
                            while ($row = mysqli_fetch_assoc($list)) { ?>

                                <tr>

                                    <td>

                                        <?php echo $row["FirstName"] . " " . $row["LastName"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["CompanyName"]; ?>

                                    </td>

                                    <td>

                                        <?php echo $row["RegistrationDate"]; ?>

                                    </td>

                                </tr>

                                <?php

                            }

                            ?>

                        </table>

                        <br>

                        <?php

                    }

                    ?>

                </div>

                <?php /*============================================================= REPORT 4
                  ==============================================================*/ $summary =
                    $registrationModel->trainingSummary(); ?>

                <div class="card">

                    <h2>Training Summary Report</h2>

                    <table>

                        <tr>

                            <th>Training</th>

                            <th>Participants</th>

                            <th>Total Revenue</th>

                        </tr>

                        <?php while ($row = mysqli_fetch_assoc($summary)) { ?>

                            <tr>

                                <td>

                                    <?php echo $row["TrainingName"]; ?>

                                </td>

                                <td>

                                    <?php echo $row["TotalParticipants"]; ?>

                                </td>

                                <td>

                                    R

                                    <?php echo number_format($row["TotalRevenue"], 2); ?>

                                </td>

                            </tr>

                            <?php

                        }

                        ?>

                    </table>

                </div>

                <?php

                break;

        }
        ?>

    </div>

    <center>


        <!-- Iframe for PHP Source Code -->
        <iframe src="task4.txt" height="400" width="1200">

            Your browser does not support iframes.

        </iframe>

    </center>

</body>

</html>