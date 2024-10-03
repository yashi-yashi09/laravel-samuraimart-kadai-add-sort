<?php
 $dsn = 'mysql:dbname=php_book_app;host=localhost;charset=utf8mb4';
 $user = 'root';
 $password = '';

  // submitパラメータの値が存在するとき（「更新」ボタンを押したとき）の処理
  if (isset($_POST['submit'])) {
    try {
        $pdo = new PDO($dsn, $user, $password);

        // 動的に変わる値をプレースホルダに置き換えたUPDATE文をあらかじめ用意する
        $sql_update = '
            UPDATE booksk1
            SET book_code = :book_code,
            book_name = :book_name,
            price = :price,
            stock_quantity = :stock_quantity,
            genre_code = :genre_code
            WHERE id = :id
        ';
        $stmt_update = $pdo->prepare($sql_update);

        // bindValue()メソッドを使って実際の値をプレースホルダにバインドする（割り当てる）
        $stmt_update->bindValue(':book_code', $_POST['book_code'], PDO::PARAM_INT);
        $stmt_update->bindValue(':book_name', $_POST['book_name'], PDO::PARAM_STR);
        $stmt_update->bindValue(':price', $_POST['price'], PDO::PARAM_INT);
        $stmt_update->bindValue(':stock_quantity', $_POST['stock_quantity'], PDO::PARAM_INT);
        $stmt_update->bindValue(':genre_code', $_POST['genre_code'], PDO::PARAM_INT);
        $stmt_update->bindValue(':id', $_GET['id'], PDO::PARAM_INT);

        // SQL文を実行する
        $stmt_update->execute();

        // 更新した件数を取得する
        $count = $stmt_update->rowCount();

        $message = "商品を{$count}件編集しました。";

        // 商品一覧ページにリダイレクトさせる（同時にmessageパラメータも渡す）
        header("Location: read.php?message={$message}");
    } catch (PDOException $e) {
        exit($e->getMessage());
    }
}

 
 // idパラメータの値が存在すれば処理を行う
 if (isset($_GET['id'])) {
     try {
         $pdo = new PDO($dsn, $user, $password);
 
         // idカラムの値をプレースホルダ（:id）に置き換えたSQL文をあらかじめ用意する
         $sql_select_product = 'SELECT * FROM booksk1 WHERE id = :id';
         $stmt_select_product = $pdo->prepare($sql_select_product);
 
         // bindValue()メソッドを使って実際の値をプレースホルダにバインドする（割り当てる）
         $stmt_select_product->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
 
         // SQL文を実行する
         $stmt_select_product->execute();
 
         // SQL文の実行結果を配列で取得する
         // 補足：1つのレコード（横1行のデータ）のみを取得したい場合、fetch()メソッドを使えばカラム名がキーになった1次元配列を取得できる    
         $product = $stmt_select_product->fetch(PDO::FETCH_ASSOC);
 
         // idパラメータの値と同じidのデータが存在しない場合はエラーメッセージを表示して処理を終了する
         // 補足：fetch()メソッドは実行結果が取得できなかった場合にFALSEを返す
         if ($product === FALSE) {
             exit('idパラメータの値が不正です。');
         }
 
         // vendorsテーブルからvendor_codeカラムのデータを取得するためのSQL文を変数$sql_select_vendor_codesに代入する
         $sql_select_vendor_codes = 'SELECT genre_code FROM genrek1';
 
         // SQL文を実行する
         $stmt_select_vendor_codes = $pdo->query($sql_select_vendor_codes);
 
         // SQL文の実行結果を配列で取得する
         // 補足：PDO::FETCH_COLUMNは1つのカラムの値を1次元配列（多次元ではない普通の配列）で取得する設定である
         $vendor_codes = $stmt_select_vendor_codes->fetchAll(PDO::FETCH_COLUMN);
     } catch (PDOException $e) {
         exit($e->getMessage());
     }
 } else {
     // idパラメータの値が存在しない場合はエラーメッセージを表示して処理を停止する
     exit('idパラメータの値が存在しません。');
 }
 ?>
 
 <!DOCTYPE html>
 <html lang="ja">
 
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>書籍編集</title>
     <link rel="stylesheet" href="css/style.css">
 
     <!-- Google Fontsの読み込み -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">
 </head>
 
 <body>
     <header>
         <nav>
             <a href="index.php">書籍管理アプリ</a>
         </nav>
     </header>
     <main>
         <article class="registration">
             <h1>書籍編集</h1>
             <div class="back">
                 <a href="read.php" class="btn">&lt; 戻る</a>
             </div> <!-- 取得した実行結果を各フォームのvalue属性に埋め込み、
                     PHPのecho文（省略形）で出力する -->
             <form action="update.php?id=<?= $_GET['id'] ?>" method="post" class="registration-form">
                 <div> 
                     <label for="book_code">書籍コード</label>
                     <input type="number" id="product_code" name="book_code" value="<?= $product['book_code'] ?>" min="0" max="100000000" required>
 
                     <label for="book_name">書籍名</label>
                     <input type="text" id="product_name" name="book_name" value="<?= $product['book_name'] ?>" maxlength="50" required>
 
                     <label for="price">単価</label>
                     <input type="number" id="price" name="price" value="<?= $product['price'] ?>" min="0" max="100000000" required>
 
                     <label for="stock_quantity">在庫数</label>
                     <input type="number" id="stock_quantity" name="stock_quantity" value="<?= $product['stock_quantity'] ?>" min="0" max="100000000" required>
 
                     <label for="vgenre_code">ジャンルコード</label>
                     <select id="vendor_code" name="genre_code" required>
                         <option disabled selected value>選択してください</option>
                         <?php
                         // 配列の中身を順番に取り出し、セレクトボックスの選択肢として出力する
                         foreach ($vendor_codes as $vendor_code) {
                             // もし変数$vendor_codeが商品の仕入先コードの値と一致していれば、
                             //selected属性をつけて初期値にする
                             //<option>タグのselected属性を使用すると、ページを読み込んだタイミングで選択肢の中から
                             //最初の選択肢を予め設定することが可能
                             if ($vendor_code === $product['vendor_code']) {
                                 echo "<option value='{$vendor_code}' selected>{$vendor_code}</option>";
                             } else {
                                 echo "<option value='{$vendor_code}'>{$vendor_code}</option>";
                             }
                         }
                         ?>
                     </select>
                 </div>
                 <button type="submit" class="submit-btn" name="submit" value="update">更新</button>
             </form>
         </article>
     </main>
     <footer>
         <p class="copyright">&copy; 書籍管理アプリ All rights reserved.</p>
     </footer>
 </body>
 
 </html>