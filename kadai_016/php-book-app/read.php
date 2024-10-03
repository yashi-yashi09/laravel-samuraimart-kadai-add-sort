<?php
$dsn = 'mysql:dbname=php_book_app;host=localhost;charset=utf8mb4';
$user = 'root';
$password = '';


try {
    $pdo = new PDO($dsn, $user, $password);

    // orderパラメータの値が存在すれば（並び替えボタンを押したとき）
    //その値を変数$orderに代入する
    if (isset($_GET['order'])) {
        $order = $_GET['order'];
    } else {
        $order = NULL;
    }

    // keywordパラメータの値が存在すれば（商品名を検索したとき）
    // その値を変数$keywordに代入する
    if (isset($_GET['keyword'])) {
        $keyword = $_GET['keyword'];
    } else {
        $keyword = NULL;
    }

    // orderパラメータの値によってSQL文を変更する
    // もしorderパラメータの値が降順なら
    if ($order === 'desc') { 
        $sql_select = 'SELECT * FROM booksk1 WHERE book_name LIKE :keyword ORDER BY updated_at DESC';
    } else {
        $sql_select = 'SELECT * FROM booksk1 WHERE book_name LIKE :keyword ORDER BY updated_at ASC';
    }

    // SQL文を用意する
    $stmt_select = $pdo->prepare($sql_select);

    // SQLのLIKE句で使うため、変数$keyword（検索ワード）の前後を%で囲む（部分一致）
    // 補足：partial match＝部分一致
    $partial_match = "%{$keyword}%";

    // bindValue()メソッドを使って実際の値をプレースホルダにバインドする（割り当てる）
    $stmt_select->bindValue(':keyword', $partial_match, PDO::PARAM_STR);
 
    // SQL文を実行する (prepareステートメントの実行)
    $stmt_select->execute();

    // SQL結果を配列で取得 fetchAllで該当するすべてのデータを配列で返す必
    $products = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit($e->getMessage());
}
?>

<!DOCTYPE html>
 <html lang="ja">
 
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>書籍一覧</title>
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
        <article class="products">
            <h1>書籍一覧</h1>
            <?php
            // （商品の登録・編集・削除後）messageパラメータの値を受け取っていれば、それを表示する
            if(isset($_GET['message'])) {
                echo "<p class='success'>{$_GET['message']}</p>";
            }
            ?>
            <div class="products-ui">
             <div>
                <!-- ここに並び替えボタンと検索ボックスを作成する -->

                 <!-- 降順に並び替えたいときのリンクを指定 
                  画像クリックしたときに指定されたURLのアクセスを行う
                  order=descはデータの並び替えの基準をサーバーに伝えている-->
                 <a href="read.php?order=desc&keyword=<?= $keyword ?>">
                 <!-- alt="" を追加すると、画像の代わりになるテキストを指定-->
                        <img src="images/desc.png" alt="降順に並び替え" class="sort-img">
                 </a> 
                 <a href="read.php?order=asc&keyword=<?= $keyword ?>">
                         <img src="images/asc.png" alt="昇順に並び替え" class="sort-img">
                </a>
                <form action="read.php" method="get" class="search-form">
                            <input type="hidden" name="order" value="<?= $order ?>">
                         <input type="text" class="search-box" placeholder="商品名で検索" name="keyword" value="<?= $keyword ?>">
                </form>

             </div>
             <a href="create.php" class="btn">書籍登録</a>
            </div>
            <table class="products-table">
                <tr>
                    <th>書籍コード</th>
                    <th>書籍名</th>
                    <th>単価</th>
                    <th>在庫数</th>
                    <th>ジャンルコード</th>
                    <th>編集</th>
                    <th>削除</th>
                </tr>

                <?php
                // 配列の中身を順番に取り出し、表形式で出力する
                foreach ($products as $product) {
                    $table_row = "
                    <tr>
                    <td>{$product['book_code']}</td>
                    <td>{$product['book_name']}</td>
                    <td>{$product['price']}</td>
                    <td>{$product['stock_quantity']}</td>
                    <td>{$product['genre_code']}</td>
                    <td><a href='update.php?id={$product['id']}'><img src='images/edit.png' alt='編集' class='edit-icon'></a></td>
                     <td><a href='delete.php?id={$product['id']}'><img src='images/delete.png' alt='削除' class='delete-icon'></a></td>
                    </tr>
                    ";
                    echo $table_row;
                }
                ?>
            </table>
        </article>
    </main>
    <footer>
    <p class="copyright">&copy; 書籍管理アプリ All rights reserved.</p>
    </footer>
 </body>

 </html>