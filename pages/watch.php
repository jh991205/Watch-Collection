<?php
include("includes/init.php");

$document_id = (int)trim($_GET['id']);
$url = "/home/watch?" . http_build_query(array('id' => $document_id));

$delete_path = 'public/uploads/' . $document_id . '.' . 'jpg';

$edit_authorization = False;
$deleted = False;

if (is_user_logged_in()) {
  if ($is_admin) {
    $edit_authorization = True;
  }
}

if ($document_id) {
  $records = exec_sql_query(
    $db,
    "SELECT * FROM watches WHERE id = :id;",
    array(':id' => $document_id)
  )->fetchAll();
  if (count($records) > 0) {
    $document = $records[0];
  } else {
    $document = NULL;
  }
}

if (isset($_POST['save'])) {
  if ($edit_authorization) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);

    if (!empty($name) and is_numeric($price)) {
      exec_sql_query(
        $db,
        "UPDATE watches SET name = :name, price = :price WHERE (id = :id);",
        array(
          'id' => $document_id,
          'name' => $name,
          'price' => $price
        )
      );
      // get updated document
      $records = exec_sql_query(
        $db,
        "SELECT * FROM watches WHERE id = :id;",
        array(':id' => $document_id)
      )->fetchAll();
      $document = $records[0];
    }
  }
}

if (isset($_POST['add_tag'])) {
  if ($edit_authorization) {

    $tag_name = trim($_POST['tag_name']);
    if(!empty($tag_name)) {
      $results = exec_sql_query(
        $db,
        "SELECT * FROM tags WHERE tags = :tags;",
        array(':tags' => $tag_name,
      ))->fetchAll();

      if(count($results) == 0) {
        $result = exec_sql_query(
          $db,
          "INSERT INTO tags (tags) VALUES (:tags);",
          array(
            ':tags' => $tag_name,
          ));
      }

      $results = exec_sql_query(
        $db,
        "SELECT * FROM tags WHERE tags = :tags;",
        array(':tags' => $tag_name,
      ))->fetchAll();

      $entry = $results[0];
      $id = $entry['id'];

      $result = exec_sql_query(
        $db,
        "INSERT INTO watch_tags (watch_id,tags_id) VALUES (:watch_id, :tags_id);",
        array(
          ':watch_id' => $document_id,
          ':tags_id' => $id,
      ));
    }
  }
}

if (isset($_POST['delete_tag'])) {
  if ($edit_authorization) {
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
      "DELETE FROM watch_tags WHERE  watch_id = :id and tags_id = :tags_id;",
      array(':id' => $document_id,
            ':tags_id' => $id,
      ))->fetchAll();
  }
}

if (isset($_POST['delete'])) {
  if($edit_authorization) {
    $records = exec_sql_query(
      $db,
      "DELETE FROM watches WHERE id = :id;",
      array(':id' => $document_id)
    )->fetchAll();

    $records = exec_sql_query(
      $db,
      "DELETE FROM watch_tags WHERE watch_id = :watch_id;",
      array(':watch_id' => $document_id)
    )->fetchAll();

    $delete_path = 'public/uploads/' . $document_id . '.' . 'jpg';
    unlink($delete_path);
    $deleted = True;
  }
}

$url = "/home/watch?" . http_build_query(array('id' => $document['id']));
$edit_url = "/home/watch?" . http_build_query(array('edit' => $document['id']));

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Watch</title>

  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>

<body>
  <?php include("includes/header.php"); ?>
  <?php if(!$deleted) { ?>
  <div class = "flex-container">
  <div class = "flexbox1">
  <section>
    <ul>
    <div class = "pages">
      <img src="/public/uploads/<?php echo htmlspecialchars($document['id'] . '.jpg' ); ?>" alt="<?php echo htmlspecialchars($document['title']); ?>" />
    </div>
    </ul>
  </div>
  <div class = "flexbox">
    <h2> Name: <?php echo htmlspecialchars($document['name']); ?> </h3>
    <h3> Tags:
    <?php
    $units =  exec_sql_query($db, "SELECT DISTINCT watch_id, tags.tags FROM watch_tags INNER JOIN tags ON tags.id=watch_tags.tags_id WHERE watch_id = $document_id")->fetchAll();
    foreach ($units as $unit) {
      echo htmlspecialchars($unit['tags']. " ");
    } ?>
    </h3>

    <h3> Description: </h3>
    <p> Watch from <?php echo htmlspecialchars($document['brand']); ?> with a price of $<?php echo htmlspecialchars($document['price']); ?> </p>

    <?php if($edit_authorization) { ?>
    <h3> Edit Watch Information </h3>
    <form class="edit" action="<?php echo htmlspecialchars($url); ?>" method="post" novalidate >
      <div class = "info n">
        <label> Name:
          <input type="text" name="name" value="<?php echo htmlspecialchars($document['name']); ?>" required />
        </label>
      </div>

      <div class = "info p">
        <label> Price:
          <input id="price" type="number" name="price" value="<?php echo htmlspecialchars($document['price']); ?>" required />
        </label>
      </div>

        <button type="submit" name="save">Save</button>
      </form>

    <h3> Edit Tags </h3>
    <form class="edit" action="<?php echo htmlspecialchars($url); ?>" method="post" novalidate >
    <div class = "info a">
      <label> Add Tag:
        <input type="text" name="tag_name" required />
      </label>
      <button type="submit" name="add_tag">Add Tag</button>
    </form>
    </div>

    <form class="edit" action="<?php echo htmlspecialchars($url); ?>" method="post" novalidate >
    <div class = "info">
      <label> Delete Tag:
        <input type="text" name="delete_tag_name" required />
      </label>
      <button type="submit" name="delete_tag">Delete Tag</button>
    </form>
    </div>

    <h3> Delete Current Watch </h3>
    <form id="delete" action="<?php $url ?>" method="post" novalidate>

    <button type="submit" name="delete">Delete Watch</button>
  </form>

  <?php } else { ?>
    <h3>Log in to edit the watch</h3>
  <?php } ?>
  </div>
  </div>
  </section>





<?php } else { ?>
  <h3> file deleted </h3>
<?php } ?>

  <?php include("includes/footer.php"); ?>
</body>

</html>
