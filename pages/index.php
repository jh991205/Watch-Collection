<?php
// open connection to database
include("includes/init.php");

$name_feedback_class = 'hidden';
$brand_feedback_class = 'hidden';
$type_feedback_class = 'hidden';
$price_feedback_class = 'hidden';
$upload_feedback_class = 'hidden';

$name = '';
$brand = '';
$type = '';
$price = '';
$upload = '';
$value = null;
$upload_filename = NULL;
$upload_ext = NULL;
$search = False;

$sticky_name = '';
$sticky_brand = '';
$sticky_type = '';
$sticky_price = '';

define("MAX_FILE_SIZE", 1000000); //1mb

if (isset($_POST['add_watch'])) {
  if (is_user_logged_in() and $is_admin) {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $type = trim($_POST['type']);
    $price = trim($_POST['price']);
    $upload = $_FILES['jpg-file'];

    $form_valid = True;

    if ($upload['error'] == UPLOAD_ERR_OK) {

      $upload_filename = basename($upload['name']);

      $upload_ext = strtolower(pathinfo($upload_filename, PATHINFO_EXTENSION));
      if (!in_array($upload_ext, array('jpg'))) {
        $form_valid = False;
      }
    } else {
      $form_valid = False;
    }

    if (empty($name)) {
      $form_valid = False;
      $name_feedback_class = '';
    }

    if (empty($brand)) {
      $form_valid = False;
      $brand_feedback_class = '';
    }

    if (empty($type)) {
      $form_valid = False;
      $type_feedback_class = '';
    }

    if (empty($price)) {
      $form_valid = False;
      $price_feedback_class = '';
    }

    if (is_numeric($price) === False) {
      $form_valid = False;
      $price_feedback_class = '';
    }

    if ($form_valid) {
      $db->beginTransaction();

      $result = exec_sql_query(
        $db,
        "INSERT INTO watches (name, brand, type, price, file_name, file_ext) VALUES (:name, :brand, :type, :price, :file_name, :file_ext);",
        array(
          ':name' => $name,
          ':brand' => $brand,
          ':type' => $type,
          ':price' => $price,
          ':file_name' => $upload_filename,
          ':file_ext' => $upload_ext,
        ));

        if ($result) {
          $record_id = $db->lastInsertId('id');
          $id_filename = 'public/uploads/' . $record_id . '.' . $upload_ext;
          move_uploaded_file($upload["tmp_name"], $id_filename);
        }

        $db->commit();

    } else {
      // form is invalid, set sticky values
      $sticky_name = $name;
      $sticky_brand = $brand;
      $sticky_type = $type;
      $sticky_price = $price;
    }
  }
}

if (isset($_POST['delete_tag'])) {
  if (is_user_logged_in() and $is_admin) {
    $delete_name = trim($_POST['delete_tag_name']);

    $results = exec_sql_query(
      $db,
      "SELECT * FROM tags WHERE tags = :tags;",
      array(':tags' => $delete_name,
    ))->fetchAll();

    $entry = $results[0];
    $id = $entry['id'];

    $records = exec_sql_query(
      $db,
      "DELETE FROM watch_tags WHERE tags_id = :tags_id;",
      array(':tags_id' => $id,
    ))->fetchAll();

    $records = exec_sql_query(
      $db,
      "DELETE FROM tags WHERE tags = :tags;",
      array(':tags' => $delete_name,
    ))->fetchAll();
  }
}

if (isset($_POST['search_by_tag'])) {
  $search = True;
  $tags = trim($_POST['tags']);

  $results = exec_sql_query(
    $db,
    "SELECT * FROM tags WHERE tags = :tags;",
    array(':tags' => $tags,
  ))->fetchAll();

  $entry = $results[0];
  $id = $entry['id'];

  $records = exec_sql_query(
    $db,
    "SELECT DISTINCT watch_id FROM watch_tags WHERE tags_id = :tags_id;",
    array(':tags_id' => $id,
  ))->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Home Page</title>
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css"/>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

</head>
<body>
  <?php include("includes/header.php"); ?>

  <div class = "flex-container">
  <div class = "flexbox">
  <section>
  <div class = "table">
    <?php if ($search == False) { ?>
    <?php $records = exec_sql_query($db, "SELECT * FROM watches")->fetchAll(); ?>
    <?php if (count($records) > 0) { ?>
        <ul>
          <?php
          foreach ($records as $record) { ?>
            <a href="/home/watch?<?php echo htmlspecialchars(http_build_query(array('id' => $record['id']))); ?>">
            <img src="/public/uploads/<?php echo $record['id'] . '.jpg' ; ?>" alt="<?php echo htmlspecialchars($record['name']); ?>" />
            <?php $term = $record['id'] ?>
            </a>
            <?php
            $units =  exec_sql_query($db, "SELECT DISTINCT watch_id, tags.tags FROM watch_tags INNER JOIN tags ON tags.id=watch_tags.tags_id WHERE watch_id = $term")->fetchAll();
            ?>
          <?php } ?>
        </ul>
        <!-- Source: rolex.com -->
        <!-- Source: iwc.com -->
        <!-- Source: swatch.com -->
        <!-- Source: longines.com -->
        <!-- Source: piaget.com -->
        <!-- Source: cartier.com -->
      <?php } else { ?>
        <p>No watches are found.</p>
      <?php } ?>
    <?php } else { ?>
    <?php if (count($records) > 0) { ?>
      <ul>
        <?php
        foreach ($records as $record) { ?>
            <a href="/home/watch?<?php echo htmlspecialchars(http_build_query(array('id' => $record['watch_id']))); ?>">
            <img src="/public/uploads/<?php echo htmlspecialchars($record['watch_id'] . '.jpg') ; ?>" alt="<?php echo htmlspecialchars($record['id']); ?>" />
            <?php $term = $record['watch_id'] ?>
            </a>
            <?php
            $units =  exec_sql_query($db, "SELECT DISTINCT watch_id, tags.tags FROM watch_tags INNER JOIN tags ON tags.id=watch_tags.tags_id WHERE watch_id = $term")->fetchAll();
            ?>
        <?php }?>
        </ul>
      <?php } else { ?>
        <p> No Watches Found </p>
    <?php } ?>
  <?php } ?>
  </div>
    </section>
  </div>

  <div class = flexbox>
  <section>
    <?php $tag_names = exec_sql_query(
    $db,
    "SELECT * FROM tags"
    )->fetchAll();
    ?>
    <h2>Search by Tag</h2>
    <form class="edit" action="/" method="post" novalidate >
    <?php
    foreach ($tag_names as $tag_name) { ?>
        <input type="radio" name="tags" value="<?php echo htmlspecialchars($tag_name['tags']); ?>">
        <label for="<?php echo htmlspecialchars($tag_name['tags']); ?>"><?php echo htmlspecialchars($tag_name['tags']); ?></label><br>
    <?php } ?>

    <button type="submit" name="search_by_tag">Search By Tag</button>
    </form>

    </section>

  <?php if ($is_admin and is_user_logged_in()) { ?>
    <section>
    <div class = "add">
      <h2>Add A Watch</h2>

      <form id="add" action="/home" method="post" enctype="multipart/form-data" novalidate>

      <div class = "info namew">
        <label for="name">Name of Watch:</label>
        <input id="name" type="text" name="name" value="<?php echo htmlspecialchars($sticky_name); ?>" required />
        <p id="name_feedback" class="feedback <?php echo htmlspecialchars($name_feedback_class); ?>">Please provide a the name of your watch.</p>
      </div>

      <div class = "info brandw">
        <label for="brand">Brand of Watch:</label>
        <input id="brand" type="text" name="brand" value="<?php echo htmlspecialchars($sticky_brand); ?>" required />
        <p id="brand_feedback" class="feedback <?php echo htmlspecialchars($brand_feedback_class); ?>">Please provide the brand of your watch.</p>
      </div>

      <div class = "info typew">
        <label for="type">Type of Watch:</label>
        <input id="type" type="text" name="type" value="<?php echo htmlspecialchars($sticky_type); ?>" required />
        <p id="type_feedback" class="feedback <?php echo htmlspecialchars($type_feedback_class); ?>">Please provide the type of your watch.</p>
      </div>

      <div class = "info pricew">
        <label for="price">Price of Watch:</label>
        <input id="price" type="number" name="price" value="<?php echo htmlspecialchars($sticky_price); ?>" required />
        <p id="price_feedback" class="feedback <?php echo htmlspecialchars($price_feedback_class); ?>">Please provide the price of your watch.</p>
      </div>

      <div class="info imagew">
        <label for="upload">JPG File:</label>
        <input id="upload" type="file" name="jpg-file" accept=".jpg" required />
        <p id="upload_feedback" class="feedback <?php echo htmlspecialchars($upload_feedback_class); ?>">Please upload a jpg image.</p>
      </div>

        <button type="submit" name="add_watch">Add Watch</button>
      </form>
    </div>
    </section>

    <section>

    <h2>Delete a Tag</h2>
    <form class="edit" action="/" method="post" novalidate >
      <label> Delete Tag:
        <input type="text" name="delete_tag_name" required />
      </label>
    </br>
      <button type="submit" name="delete_tag">Delete Tag</button>
    </form>
    </section>

  <?php } else { ?>
    <h3>Log in to edit watches and tags</h3>
  <?php } ?>

  </div>
  </div>

  <?php include("includes/footer.php"); ?>

</body>

</html>
