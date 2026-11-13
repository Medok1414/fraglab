<?php
require 'config.php';

unset($_SESSION['user']);
flash('Вы вышли из аккаунта');
header('Location: index.php');
