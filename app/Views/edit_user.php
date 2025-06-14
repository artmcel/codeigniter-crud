<?= $this->extend('default') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1>Edit User</h1>
        <form action="<?= base_url('users/edit/' . $user['id']) ?>" method="post">
            <div class="col-md">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= esc($user['name']) ?>" required>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= esc($user['phone']) ?>" required>
                </div>
            </div>
            <div class="col-md mt-3">
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>