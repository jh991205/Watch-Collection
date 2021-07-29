<?php
include_once("includes/db.php");

// User Messages
$session_messages = array();
$signup_messages = array();

// cookie expiration time
define('SESSION_COOKIE_DURATION', 60 * 60 * 1); // 1 hour = 60 sec * 60 min * 1 hr

// find user's record from user_id
function find_user($db, $user_id)
{
  $records = exec_sql_query(
    $db,
    "SELECT * FROM users WHERE id = :user_id;",
    array(':user_id' => $user_id)
  )->fetchAll();
  if ($records) {
    // users are unique, there should only be 1 record
    return $records[0];
  }
  return NULL;
}


// find group's record from user_id
function find_group($db, $group_id)
{
  $records = exec_sql_query(
    $db,
    "SELECT * FROM groups WHERE id = :group_id;",
    array(':group_id' => $group_id)
  )->fetchAll();
  if ($records) {
    // groups are unique, there should only be 1 record
    return $records[0];
  }
  return NULL;
}


// find user's record from session hash
function find_session($db, $session)
{
  if (isset($session)) {
    $records = exec_sql_query(
      $db,
      "SELECT * FROM sessions WHERE session = :session;",
      array(':session' => $session)
    )->fetchAll();
    if ($records) {
      // sessions are unique, so there should only be 1 record
      return $records[0];
    }
  }
  return NULL;
}


// provide a function alternative to  $current_user
function current_user()
{
  global $current_user;
  return $current_user;
}


// Did the user log in?
function is_user_logged_in()
{
  global $current_user;

  // if $current_user is not NULL, then a user is logged in!
  return ($current_user != NULL);
}


// is the user a member
function is_user_member_of($db, $group_id)
{
  global $current_user;
  if ($current_user === NULL) {
    return False;
  }

  $records = exec_sql_query(
    $db,
    "SELECT id FROM memberships WHERE (group_id = :group_id) AND (user_id = :user_id);",
    array(
      ':group_id' => $group_id,
      ':user_id' => $current_user['id']
    )
  )->fetchAll();
  if ($records) {
    return True;
  } else {
    return False;
  }
}


// login with username and password
function password_login($db, &$messages, $username, $password)
{
  global $current_user;
  global $sticky_login_username;

  $username = trim($username);
  $password = trim($password);
  $sticky_login_username = $username;

  if (isset($username) && isset($password)) {
    // Does this username even exist in our database?
    $records = exec_sql_query(
      $db,
      "SELECT * FROM users WHERE username = :username;",
      array(':username' => $username)
    )->fetchAll();
    if ($records) {
      // Username is UNIQUE, so there should only be 1 record.
      $user = $records[0];

      // Check password against hash in DB
      if (password_verify($password, $user['password'])) {
        // Generate session
        $session = session_create_id();

        // Store session ID in database
        $result = exec_sql_query(
          $db,
          "INSERT INTO sessions (user_id, session, last_login) VALUES (:user_id, :session, datetime());",
          array(
            ':user_id' => $user['id'],
            ':session' => $session
          )
        );
        if ($result) {
          // Success, session stored in DB

          // Send this back to the user.
          setcookie("session", $session, time() + SESSION_COOKIE_DURATION, '/');

          error_log("  login via password successful");
          $current_user = $user;
          return $current_user;
        } else {
          array_push($messages, "Log in failed.");
        }
      } else {
        array_push($messages, "Invalid username or password.");
      }
    } else {
      array_push($messages, "Invalid username or password.");
    }
  } else {
    array_push($messages, "No username or password given.");
  }

  error_log("  failed to login via password");
  $current_user = NULL;
  return $current_user;
}


// login via session cookie
function cookie_login($db, $session)
{
  global $current_user;

  if (isset($session)) {
    $current_user = find_user($db, $session['user_id']);

    // update the last login in the DB
    $result = exec_sql_query(
      $db,
      "UPDATE sessions SET last_login = datetime() WHERE (id = :session_id);",
      array(':session_id' => $session['id'])
    );

    // Renew the cookie for 1 more hour
    setcookie("session", $session['session'], time() + SESSION_COOKIE_DURATION, '/');

    error_log("  login via cookie successful");
    return $current_user;
  }

  error_log("  failed to login via cookie");
  $current_user = NULL;
  return NULL;
}


// logout
function logout()
{
  // Note: You can delete the record in the sessions table, but it's considered better practice to have a "cron" job that cleans up expired sessions.

  // Remove the session from the cookie and force it to expire (go back in time).
  setcookie('session', '', time() - SESSION_COOKIE_DURATION, '/');

  // $current_user keeps track of logged in user, set to NULL to forget.
  global $current_user;
  $current_user = NULL;

  error_log("  logout successful");
}


// logout url for the current page
function logout_url()
{
  // Add a logout query string parameter
  $params = $_GET;
  $params['logout'] = '';

  // Add logout param to current page URL.
  $logout_url = htmlspecialchars($_SERVER['PHP_SELF']) . '?' . http_build_query($params);

  return $logout_url;
}


// echo login form
function echo_login_form($action, $messages)
{
  global $sticky_login_username;
?>

  <ul class="login">
    <?php
    foreach ($messages as $message) {
      echo "<li class=\"feedback\"><strong>" . htmlspecialchars($message) . "</strong></li>\n";
    } ?>
  </ul>

  <form class="login" action="<?php echo htmlspecialchars($action) ?>" method="post" novalidate>
    <div class="group_label_input">
      <label for="username">Username:</label>
      <input id="username" type="text" name="login_username" value="<?php echo htmlspecialchars($sticky_login_username); ?>" required />
    </div>

    <div class="group_label_input">
      <label for="password">Password:</label>
      <input id="password" type="password" name="login_password" required />
    </div>

    <div class="align-right">
      <button name="login" type="submit">Sign In</button>
    </div>
  </form>
<?php
}


// Check for login, logout requests. Or check to keep the user logged in.
function process_session_params($db, &$messages)
{
  global $current_user;

  // Is there a session? If so, find it!
  $session = NULL;
  if (isset($_COOKIE["session"])) {
    $session_hash = $_COOKIE["session"];

    $session = find_session($db, $session_hash);
  }

  if (isset($_GET['logout']) || isset($_POST['logout'])) { // Check if we should logout the user
    error_log("  attempting to logout...");
    logout();
  } else if (isset($_POST['login'])) { // Check if we should login the user
    error_log("  attempting to login with username and password...");
    password_login($db, $messages, $_POST['login_username'], $_POST['login_password']);
  } else if ($session) { // check if logged in already via cookie
    error_log("  attempting to login via cookie...");
    cookie_login($db, $session);
  }
}

// alias for process_session_params
function process_login_params($db, &$messages)
{
  process_session_params($db, $messages);
}


// function to create user account
function create_account($db, $name, $username, $password, $password_confirmation)
{
  global $signup_messages;

  global $sticky_signup_username;
  global $sticky_signup_name;

  $name = trim($name);
  $username = trim($username);
  $password = trim($password);
  $password_confirmation = trim($password_confirmation);

  $sticky_signup_username = $username;
  $sticky_signup_name = $name;

  $account_valid = True;

  $db->beginTransaction();

  // check if username is unique, give error message if not.
  if (empty($username)) {
    $account_valid = False;
    array_push($signup_messages, "Please provide a username.");
  } else {
    $records = exec_sql_query(
      $db,
      "SELECT username FROM users WHERE (username = :username);",
      array(
        ':username' => $username
      )
    )->fetchAll();
    if (count($records) > 0) {
      $account_valid = False;
      array_push($signup_messages, "Username is already taken, please pick another username.");
    }
  }

  // TODO: check if password meets security requirements.
  if (empty($password)) {
    $account_valid = False;
    array_push($signup_messages, "Please provide a password.");
  }

  // Check if passwords match
  if ($password != $password_confirmation) {
    $account_valid = False;
    array_push($signup_messages, "Password confirmation doesn't match your password. Please reenter your password.");
  } else {
    // hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  }

  if ($account_valid) {
    $result = exec_sql_query(
      $db,
      "INSERT INTO users (name, username, password) VALUES (:name, :username, :password);",
      array(
        ':name' => $name,
        ':username' => $username,
        ':password' => $hashed_password
      )
    );
    if ($result) {
      // account creation was successful. Login.
      password_login($db, $messages, $username, $password);
    } else {
      array_push($messages, "Password confirmation doesn't match your password. Please reenter your password.");
    }
  }

  $db->commit();
}


// echo sign up form
function echo_signup_form($action)
{
  global $signup_messages;

  global $sticky_signup_username;
  global $sticky_signup_name;
?>

  <ul class="signup">
    <?php
    foreach ($signup_messages as $message) {
      echo "<li class=\"feedback\"><strong>" . htmlspecialchars($message) . "</strong></li>\n";
    } ?>
  </ul>

  <form class="signup" action="<?php echo htmlspecialchars($action) ?>" method="post" novalidate>
    <div class="group_label_input">
      <label for="name">Name:</label>
      <input id="name" type="text" name="signup_name" value="<?php echo htmlspecialchars($sticky_signup_name); ?>" required />
    </div>

    <div class="group_label_input">
      <label for="username">Username:</label>
      <input id="username" type="text" name="signup_username" value="<?php echo htmlspecialchars($sticky_signup_username); ?>" required />
    </div>

    <div class="group_label_input">
      <label for="password">Password:</label>
      <input id="password" type="password" name="signup_password" required />
    </div>

    <div class="group_label_input">
      <label for="confirm_password">Confirm Password:</label>
      <input id="confirm_password" type="password" name="signup_confirm_password" required />
    </div>

    <div class="align-right">
      <button name="signup" type="submit">Sign Up</button>
    </div>

  </form>
<?php
}


// Check for login, logout requests. Or check to keep the user logged in.
function process_signup_params($db)
{
  // Check if we should login the user
  if (isset($_POST['signup'])) {
    create_account($db, $_POST['signup_name'], $_POST['signup_username'], $_POST['signup_password'], $_POST['signup_confirm_password']);
  }
}
