<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title><?php echo $title; ?> Not Found</title>

  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>

<body>
  <?php include("includes/header.php"); ?>

  <main>
    <h2><?php echo $title; ?></h2>
    <div class = "a404">
      <p>You might have typed a wrong url, please check your url link</p>
    </div>
  </main>

  <?php include("includes/footer.php"); ?>
</body>

</html>
