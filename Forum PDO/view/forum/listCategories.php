
<link rel="stylesheet" href="./public/css/style.css">

<?php

$categories = $result['data']['categories'];

?>

<h1 id="categoriesListTitle">Liste des categories</h1>

<?php
foreach ($categories as $category) {
?>
    <div id="categoriesList">
        <div class="category">
            <a href="index.php?ctrl=forum&action=listTopics&id=<?= $category->getId() ?>"><p><?= $category->getCategoryName() ?></p></a>
        </div>
        <div class="crudList">
            <a href="index.php?ctrl=forum&action=deleteCategory&id=<?= $category->getId() ?>"><i class="fa-solid fa-square-minus fa-lg"></i></a>
            <a href="index.php?ctrl=forum&action=updateCategoryForm&id=<?= $category->getId() ?>"><i class="fa-sharp fa-regular fa-pen-to-square fa-lg"></i></a>
        </div>
    </div>
<?php
}
?>
<a id="addCategoryButton"href="index.php?ctrl=forum&action=addCategoryForm"><button>Add a category</button></a>


