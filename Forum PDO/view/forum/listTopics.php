<link rel="stylesheet" href="./public/css/style.css">

<?php

$topics = $result['data']['topics'];
$category = $result['data']['category'];
?>

<h1>Liste des topics</h1>

<div class="content">

    <?php foreach($topics as $topic) : ?>

        <div class="card">
            <div class="card-header">
                <a href="index.php?ctrl=forum&action=listPosts&id=<?= $topic->getId() ?>">
                    <p><?= $topic->getTitle() ?></p>
                </a>
            </div>
            <?php if (App\Session::isAdmin() || App\Session::getUser() == $topic->getUser()) :?>
                <div class="card-body">
                    <?php if (!$topic->getIsLocked()) :?>
                        
                            <a href="index.php?ctrl=forum&action=lockTopic&id=<?= $topic->getId() ?>">
                                <i class="fa-solid fa-lock-open fa-sm"></i>
                            </a>
                        
                    <?php else :?>
                        
                            <a href="index.php?ctrl=forum&action=unlockTopic&id=<?= $topic->getId() ?>">
                            <i class="fa-solid fa-lock fa-sm"></i>
                            </a>
                        
                    <?php endif ?>
                        <a href="index.php?ctrl=forum&action=deleteTopic&id=<?= $topic->getId() ?>">
                        <i class="fa-solid fa-trash fa-sm"></i>
                        </a>
                </div>
            <?php endif ?>
        </div>

    <?php endforeach ?>
</div>

<div class="addTopic">
    <?php  if (App\Session::isAdmin() || App\Session::getUser()) :?> 
        <a href="index.php?ctrl=forum&action=addTopicForm&id=<?= $category->getId() ?>">
            <button>Add a topic</button>
        </a>
    <?php endif ?>
</div>
