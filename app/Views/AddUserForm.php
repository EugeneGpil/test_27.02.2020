<form method="POST">
    <input
        type="text"
        placeholder="Имя пользователя"
        name="username"
        value="<?= $data["username"] ?>"
    >
    <select name="role_id">
        <option
            disabled 
            hidden
            <?= !$data["role_id"] ? "selected" : "" ?>
        >
            Выберите роль
        </option>
        <?php foreach ($data["roles"] as $role) { ?>
            <option
                <?= $data["role_id"] == $role["id"] ? "selected" : "" ?>
                value="<?= $role["id"] ?>"
            >
                <?= $role["role"] ?>
            </option>
        <?php } ?>
    </select>
    <input type="submit">
    <div><?= $data["message"] ?></div>
    <div class="add-user-role-form__error"><?= $data["error"] ?></div>
</form>
<a href="/">На главную</a>