<form method="POST">
    <input
        type="text"
        placeholder="Роль"
        name="role"
        value="<?= $data["role"] ?>"
    >
    <input type="submit">
    <div><?= $data["message"] ?></div>
    <div class="add-user-role-form__error"><?= $data["error"] ?></div>
</form>
<a href="/">На главную</a>