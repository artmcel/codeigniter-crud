<?= $this->extend('default') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <h1>Welcome to the Main Page</h1>
        <p>This is the main content area.</p>
        <a class="btn btn-primary" href="<?= base_url('users') ?>">Ver Usuarios</a>
    </div>
</div>
<?= $this->endSection() ?>