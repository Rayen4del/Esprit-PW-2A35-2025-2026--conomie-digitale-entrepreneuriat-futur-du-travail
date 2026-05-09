<?php
$users = [
 'moomen' => '$2y$10$FNomztD.06rEgvRmalUt.ef/y2xoBSLGejHtImrbawFCzIOjAdIpe',
 'admin' => '$2y$10$5y2kmDWrFI5VjLxapEsCduQY4qXcyWWwHADrZjugU8cYF51j617le',
 'propro' => '$2y$10$45/ofeZuLefkgkwN8KrniuSoXS.9doXelXaoevOM4/HSQxyQ1hRDS',
 'monji' => '$2y$10$SsRY9YBsQGSLLLvWevOAxuBDYA2ttrmW5pczTeEwlM8EhdcUnb3iy',
 'pro2' => '$2y$10$1bgGH3o0zln5fVrINhkolOT9/Fncb3Ymd0Yb777JCPzXv7fWW50nO',
];
$candidates = ['admin','admin123','super123','user123','password','123456','12345678','1234','0000','moomen','moomen123','abdelmoomen','abdelmoomen123','propro','propro123','pro123','professionnel','monji','monji123','pro2','pro2123','test','azerty','azerty123'];
foreach ($users as $name => $hash) {
  foreach ($candidates as $password) {
    if (password_verify($password, $hash)) {
      echo "$name=$password\n";
      continue 2;
    }
  }
  echo "$name=NOT_FOUND\n";
}
?>
