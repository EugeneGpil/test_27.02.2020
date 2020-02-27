<div>
    <?php foreach($data["users"] as $user) { ?>   
        <div  class="users-list__table-row">
            <div><?= $user["username"] ?></div>
            <div><?= $user["role"] ?></div>
        </div>
    <?php } ?>
</div>

<?php if ($data["previousPage"]) { ?>
    <a href="/users_list/<?= $data["previousPage"] ?>"><--</a>
<?php } ?>

<a href="/users_list/<?= $data["currentPage"] ?>"><?= $data["currentPage"] ?></a>

<?php if ($data["nextPage"]) { ?>
    <a href="/users_list/<?= $data["nextPage"] ?>">--></a>
<?php } ?>

<br><a href="/">На главную</a>