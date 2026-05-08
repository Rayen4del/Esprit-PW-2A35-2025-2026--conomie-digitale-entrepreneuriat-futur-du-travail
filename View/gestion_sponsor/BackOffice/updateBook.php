<?php
include '../../controller/BookController.php';
require_once __DIR__ . '/../../Model/Book.php';

$error = '';
$book = null;
$bookC = new BookController();

// Récupérer les données du livre à modifier
if(isset($_POST['id'])) {
    $book = $bookC->showBook($_POST['id']);
}

if (
    isset($_POST['id'], $_POST['title'], $_POST['author'], $_POST['publication_date'], $_POST['language'], $_POST['status'], $_POST['copies'], $_POST['category'])
) {
    if (
        !empty($_POST['id']) && !empty($_POST['title']) && !empty($_POST['author']) && !empty($_POST['publication_date'])
        && !empty($_POST['language']) && !empty($_POST['status']) && !empty($_POST['copies']) && !empty($_POST['category'])
    ) {
        $book = new Book(
            $_POST['id'],
            $_POST['title'] ?? '',
            $_POST['author'] ?? '',
            new DateTime($_POST['publication_date'] ?? 'now'),
            $_POST['language'] ?? 'FR',     // langue
            $_POST['status'] ?? 'Disponible',
            ($_POST['copies'] ?? 1),
            $_POST['category'] ?? 'Science'
        );
        $bookC->updateBook($book, $_POST['id']);
        header('Location:bookList.php');
        exit();
    }
    
    else
    {
        $error= 'please enter data';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
 <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <title>Esprit Book | Update</title>
  

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/lineicons.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->

    <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
      <div class="navbar-logo">
        <a href="bookList.php">
          <img src="images/logoEspritBook.png" alt="logo" width="40%" height="70%" />
        </a>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li class="nav-item nav-item-has-children">
            <a
              href="#0"
              data-bs-toggle="collapse"
              data-bs-target="#ddmenu_1"
              aria-controls="ddmenu_1"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="icon">
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
       xmlns="http://www.w3.org/2000/svg">
    <path
      d="M8.74999 18.3333C12.2376 18.3333 15.1364 15.8128 15.7244 12.4941C15.8448 11.8143 15.2737 11.25 14.5833 11.25H9.99999C9.30966 11.25 8.74999 10.6903 8.74999 10V5.41666C8.74999 4.7263 8.18563 4.15512 7.50586 4.27556C4.18711 4.86357 1.66666 7.76243 1.66666 11.25C1.66666 15.162 4.83797 18.3333 8.74999 18.3333Z"
      fill="red" />
    <path
      d="M17.0833 10C17.7737 10 18.3432 9.43708 18.2408 8.75433C17.7005 5.14918 14.8508 2.29947 11.2457 1.75912C10.5629 1.6568 10 2.2263 10 2.91665V9.16666C10 9.62691 10.3731 10 10.8333 10H17.0833Z"
      fill="red" />
  </svg>
</span>

              <span class="text">Dashboard</span>
            </a>
            <ul id="ddmenu_1" class="collapse show dropdown-nav">
              <li>
                <a href="bookList.php" class="active"> Book Store </a>
              </li>
            </ul>
          </li>
           <li class="nav-item nav-item-has-children">
            <a
              href="#0"
              class="collapsed"
              data-bs-toggle="collapse"
              data-bs-target="#ddmenu_5"
              aria-controls="ddmenu_5"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
             <span class="icon">
  <svg width="20" height="20" viewBox="0 0 20 20" 
       xmlns="http://www.w3.org/2000/svg">
    <path
      d="M4.16666 3.33335C4.16666 2.41288 4.91285 1.66669 5.83332 1.66669H14.1667C15.0872 1.66669 15.8333 2.41288 15.8333 3.33335V16.6667C15.8333 17.5872 15.0872 18.3334 14.1667 18.3334H5.83332C4.91285 18.3334 4.16666 17.5872 4.16666 16.6667V3.33335ZM6.04166 5.00002C6.04166 5.3452 6.32148 5.62502 6.66666 5.62502H13.3333C13.6785 5.62502 13.9583 5.3452 13.9583 5.00002C13.9583 4.65485 13.6785 4.37502 13.3333 4.37502H6.66666C6.32148 4.37502 6.04166 4.65485 6.04166 5.00002ZM6.66666 6.87502C6.32148 6.87502 6.04166 7.15485 6.04166 7.50002C6.04166 7.8452 6.32148 8.12502 6.66666 8.12502H13.3333C13.6785 8.12502 13.9583 7.8452 13.9583 7.50002C13.9583 7.15485 13.6785 6.87502 13.3333 6.87502H6.66666ZM6.04166 10C6.04166 10.3452 6.32148 10.625 6.66666 10.625H9.99999C10.3452 10.625 10.625 10.3452 10.625 10C10.625 9.65485 10.3452 9.37502 9.99999 9.37502H6.66666C6.32148 9.37502 6.04166 9.65485 6.04166 10ZM9.99999 16.6667C10.9205 16.6667 11.6667 15.9205 11.6667 15C11.6667 14.0795 10.9205 13.3334 9.99999 13.3334C9.07949 13.3334 8.33332 14.0795 8.33332 15C8.33332 15.9205 9.07949 16.6667 9.99999 16.6667Z"
      fill="red" />
  </svg>
</span>

              <span class="text"> Book Management </span>
            </a>
            <ul id="ddmenu_5" class="collapse dropdown-nav">
              <li>
                <a href="bookList.php"> Book List</a>
              </li>
              <li>
                <a href="addBook.php"> ADD</a>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
     
    </aside>
    <div class="overlay"></div>
 <main class="main-wrapper">
      <!-- ========== header start ========== -->
      <header class="header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-5 col-md-5 col-6">
              <div class="header-left d-flex align-items-center">
                <div class="menu-toggle-btn mr-15">
                  <button id="menu-toggle" class="main-btn danger-btn btn-hover">
                    <i class="lni lni-chevron-left me-2"></i> Menu
                  </button>
                </div>
                <div class="header-search d-none d-md-flex">
                  <form action="#">
                    <input type="text" placeholder="Search..." />
                    <button><i class="lni lni-search-alt"></i></button>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-lg-7 col-md-7 col-6">
              <div class="header-right">
                
              
                <div class="profile-box ml-15">
                  <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-info">
                      <div class="info">
                        <div class="image">
                          <img src="assets/images/profile/profile-image.png" alt="" />
                        </div>
                        <div>
                          <h6 class="fw-500">Book Store</h6>
                          <p>Admin</p>
                        </div>
                      </div>
                    </div>
                  </button>
                 
                </div>
                <!-- profile end -->
              </div>
            </div>
          </div>
        </div>
      </header>
      <!-- ========== header end ========== -->

      <!-- ========== section start ========== -->
      <section class="section">
        <div class="container-fluid">
          <!-- ========== title-wrapper start ========== -->
          <div class="title-wrapper pt-30">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="title">
                  <h2>Update book page</h2>
                </div>
              </div>
              <!-- end col -->
              <div class="col-md-6">
                <div class="breadcrumb-wrapper">
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a href="bookList.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item active" aria-current="page">
                       Update
                      </li>
                    </ol>
                  </nav>
                </div>
              </div>
              <!-- end col -->
            </div>
            <!-- end row -->
          </div>
       <div class="content">
    
    <!-- Topbar -->
  

    <!-- Form Card -->
    <div class="container mt-4">
      
        <form action="" method="POST">
          <!-- ID (Hidden) -->
          <input type="hidden" name="id" value="<?php echo $_POST['id'] ?? ($book['id'] ?? ''); ?>">

          <!-- Title -->
          <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Enter book title" value="<?php echo $book['title'] ?? ''; ?>" required>
          </div>

          <!-- Author -->
          <div class="mb-3">
            <label for="author" class="form-label">Author</label>
            <input type="text" class="form-control" id="author" name="author" placeholder="Enter author's name" value="<?php echo $book['author'] ?? ''; ?>" required>
          </div>

          <!-- Publication Date -->
          <div class="mb-3">
            <label for="publication_date" class="form-label">Publication Date</label>
            <input type="date" class="form-control" id="publication_date" name="publication_date" value="<?php echo $book['publication_date'] ?? ''; ?>" required>
          </div>

          <!-- Language -->
          <div class="mb-3">
            <label class="form-label">Language</label><br>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" id="langAN" name="language" value="AN" <?php echo (isset($book['langue']) && $book['langue']==='AN')?'checked':''; ?>>
              <label class="form-check-label" for="langAN">AN</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" id="langFR" name="language" value="FR" <?php echo (isset($book['langue']) && $book['langue']==='FR')?'checked':''; ?>>
              <label class="form-check-label" for="langFR">FR</label>
            </div>
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="Disponible" <?php echo (isset($book['status']) && $book['status']==='Disponible')?'selected':''; ?>>Available</option>
              <option value="Indisponible" <?php echo (isset($book['status']) && $book['status']==='Indisponible')?'selected':''; ?>>Unavailable</option>
            </select>
          </div>

          <!-- Number of Copies -->
          <div class="mb-3">
            <label for="copies" class="form-label">Number of Copies</label>
            <input type="number" class="form-control" id="copies" name="copies" placeholder="Enter number of copies" value="<?php echo $book['copies'] ?? '1'; ?>" min="1" required>
          </div>

          <!-- Category -->
          <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" id="category" name="category">
              <option value="Science" <?php echo (isset($book['category']) && $book['category']==='Science')?'selected':''; ?>>Science</option>
              <option value="Literature" <?php echo (isset($book['category']) && $book['category']==='Literature')?'selected':''; ?>>Literature</option>
              <option value="Technology" <?php echo (isset($book['category']) && $book['category']==='Technology')?'selected':''; ?>>Technology</option>
              <option value="History" <?php echo (isset($book['category']) && $book['category']==='History')?'selected':''; ?>>History</option>
              <option value="Arts" <?php echo (isset($book['category']) && $book['category']==='Arts')?'selected':''; ?>>Arts</option>
            </select>
          </div>

          <!-- Submit -->
          <div class="text-center">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-pencil-square"></i> Update Book
            </button>
            <a href="bookList.php" class="btn btn-secondary ms-2">
              <i class="bi bi-arrow-left"></i> Cancel
            </a>
          </div>
        </form>

     
    </div>

  </div>
        </div>
        <!-- end container -->
      </section>
      <!-- ========== section end ========== -->

      <!-- ========== footer start =========== -->
      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-6 order-last order-md-first">
              <div class="copyright text-center text-md-start">
                <p class="text-sm">
                  Designed and Developed by
                  
                    Esprit Student 
               
                </p>
              </div>
            </div>
            <!-- end col-->
            <div class="col-md-6">
              <div class="terms d-flex justify-content-center justify-content-md-end">
                <a href="#0" class="text-sm">Term & Conditions</a>
                <a href="#0" class="text-sm ml-15">Privacy & Policy</a>
              </div>
            </div>
          </div>
          <!-- end row -->
        </div>
        <!-- end container -->
      </footer>
      <!-- ========== footer end =========== -->
    </main>

  <!-- Main content -->
 

 <!-- ========= All Javascript files linkup ======== -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/Chart.min.js"></script>
    <script src="assets/js/dynamic-pie-chart.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/fullcalendar.js"></script>
    <script src="assets/js/jvectormap.min.js"></script>
    <script src="assets/js/world-merc.js"></script>
    <script src="assets/js/polyfill.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>