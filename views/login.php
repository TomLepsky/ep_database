<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="/style/style.css" />

	<title>login</title>
</head>
<body>

  <div class="login-form">

      <form action="#" method="post">
        <div class="form-row-15">
          <div class="form-col-100">
            <label for="login">Логин</label>
            <input type="text" name="login" id="login" placeholder="Логин" required autofocus>
          </div> 
        </div>
        <div class="form-row-15">
          <div class="form-col-100">
            <label for="password">Пароль</label>
            <input type="password" name="password" id="password" placeholder="Пароль" required>
          </div> 
        </div>
        <div class="form-row-10">
          <div class="form-col-100">
            <label for="checkbox"> Запомнить меня </label>
            <input type="checkbox" id="checkbox" value="remember-me">
          </div>
        </div>
        <div class="form-row-10">
          <div class="form-col-100">
           <input type="submit" name="submit" value="Войти">
          </div>
        </div>   
      </form>

    </div> <!-- /container -->  
  <?php if ($errors) echo $errors; ?>
	
</body>
</html>