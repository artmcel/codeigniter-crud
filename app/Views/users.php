<?= $this->extend('default') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1>Users</h1>
        <p>List of users in the system:</p>
        <p>
            <a href="<?= base_url('users/create') ?>" class="btn btn-primary">Create New User</a>
        </p>
        <?php if (empty($users)): ?>
            <p>No users found.</p>
            <p>
                <a href="<?= base_url('users/create') ?>" class="btn btn-primary">Create New User</a>
            </p>
        <?php else: ?>
        <table class="table table-md table-striped table-hover">
            <thead class="text-center">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= esc($user['id']) ?></td>
                        <td><?= esc($user['name']) ?></td>
                        <td><?= esc($user['phone']) ?></td>
                        <td class="justify-content-center">
                            <a class="btn btn-warning mx-auto" href="<?= base_url('users/edit/' . $user['id']) ?>">Edit</a>
                            <a class="btn btn-danger mx-auto" href="<?= base_url('users/delete/' . $user['id']) ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>