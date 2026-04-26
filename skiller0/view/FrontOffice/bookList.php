<?php
include '../../Controller/BookController.php';
$bookC = new BookController();
$list = $bookC->listBooks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Books - EspritBook University Library</title>
</head>
<head>
	<title>Books - EspritBook University Library</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="format-detection" content="telephone=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="author" content="">
	<meta name="keywords" content="">
	<meta name="description" content="">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

	<link rel="stylesheet" type="text/css" href="assets/css/normalize.css">
	<link rel="stylesheet" type="text/css" href="assets/icomoon/icomoon.css">
	<link rel="stylesheet" type="text/css" href="assets/css/vendor.css">
<link rel="stylesheet" type="text/css" href="style.css">

</head>
<body>
    <div id="header-wrap">
    	<header id="header">
			<div class="container-fluid">
				<div class="row">

					<div class="col-md-2">
						<div class="main-logo">
							<a href="index.html"><img src="images/logoEspritBook.png" alt="logo" width="40%" height="50%"></a>
						</div>

					</div>

					<div class="col-md-10">

						<nav id="navbar">
							<div class="main-menu stellarnav">
								<ul class="menu-list">
                                    
									<li class="menu-item"><a href="index.html">Home</a></li>
									<li class="menu-item active"><a href="bookList.php">Books</a></li>
									<li class="menu-item"><a href="#about" class="nav-link">About</a></li>
									<li class="menu-item"><a href="#contact" class="nav-link">Contact</a></li>
									<li class="menu-item"><a href="../BackOffice/index.html" class="nav-link">Dashboard</a></li>

								</ul>
							</div>
						</nav>

					</div>

				</div>
			</div>
		</header>
	</div>

	<!-- ========== Hero Section Start ========== -->
	<section id="hero" class="hero">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="hero-content">
						<h1 class="h1">Our Book Collection</h1>
						<p class="hero-desc">Discover our extensive collection of books covering various topics and categories</p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- ========== Hero Section End ========== -->

	<!-- ========== Books Section Start ========== -->
	<section id="books" class="books section-padding">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="section-title">
						<h2 class="h2">Available Books</h2>
						<p>Browse through our collection of books</p>
					</div>
				</div>
			</div>
			
			<div class="row">
				<?php
				foreach($list as $book) {
					$statusClass = ($book['status'] == 'Disponible') ? 'available' : 'unavailable';
					$statusText = ($book['status'] == 'Disponible') ? 'Available' : 'Unavailable';
				?>
				<div class="col-lg-4 col-md-6 mb-4">
					<div class="book-card">
						<div class="book-image">
							<img src="images/product-item1.jpg" alt="<?php echo $book['title']; ?>" class="img-fluid">
							<div class="book-status <?php echo $statusClass; ?>">
								<?php echo $statusText; ?>
							</div>
						</div>
						<div class="book-content">
							<h4 class="book-title"><?php echo $book['title']; ?></h4>
							<p class="book-author">by <?php echo $book['author']; ?></p>
							<div class="book-details">
								<div class="book-info">
									<span class="info-label">Category:</span>
									<span class="info-value"><?php echo $book['category']; ?></span>
								</div>
								<div class="book-info">
									<span class="info-label">Language:</span>
									<span class="info-value"><?php echo $book['langue']; ?></span>
								</div>
								<div class="book-info">
									<span class="info-label">Publication Date:</span>
									<span class="info-value"><?php echo $book['publicationDate']; ?></span>
								</div>
								<div class="book-info">
									<span class="info-label">Copies Available:</span>
									<span class="info-value"><?php echo $book['copies']; ?></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				}
				?>
			</div>
		</div>
	</section>
	<!-- ========== Books Section End ========== -->

	<!-- ========== Footer Start ========== -->
	<footer id="footer" class="footer">
		<div class="container">
			<div class="row">
				<div class="col-lg-4 col-md-6">
					<div class="footer-widget">
						<div class="footer-logo">
							<a href="index.html"><img src="images/logoEspritBook.png" alt="logo" width="30%" height="40%"></a>
						</div>
						<p>EspritBook University Library - Your gateway to knowledge and learning.</p>
					</div>
				</div>
				<div class="col-lg-4 col-md-6">
					<div class="footer-widget">
						<h4 class="footer-title">Quick Links</h4>
						<ul class="footer-links">
							<li><a href="index.html">Home</a></li>
							<li><a href="bookList.php">Books</a></li>
							<li><a href="#about">About</a></li>
							<li><a href="#contact">Contact</a></li>
						</ul>
					</div>
				</div>
				<div class="col-lg-4 col-md-6">
					<div class="footer-widget">
						<h4 class="footer-title">Contact Info</h4>
						<div class="contact-info">
							<p><i class="icon-phone"></i> +1 234 567 8900</p>
							<p><i class="icon-email"></i> info@espritbook.edu</p>
							<p><i class="icon-location"></i> University Campus, City</p>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="footer-bottom">
						<p>&copy; 2024 EspritBook University Library. All rights reserved.</p>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<!-- ========== Footer End ========== -->

	<!-- ========== Scripts ========== -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
		crossorigin="anonymous"></script>
	<script src="assets/js/jquery-1.11.0.min.js"></script>
	<script src="assets/js/plugins.js"></script>
	<script src="assets/js/script.js"></script>

	<style>
		.books {
			padding: 80px 0;
		}
		
		.section-title {
			text-align: center;
			margin-bottom: 60px;
		}
		
		.section-title h2 {
			font-size: 2.5rem;
			margin-bottom: 20px;
			color: #333;
		}
		
		.section-title p {
			font-size: 1.1rem;
			color: #666;
		}
		
		.book-card {
			background: #fff;
			border-radius: 10px;
			box-shadow: 0 5px 15px rgba(0,0,0,0.1);
			overflow: hidden;
			transition: transform 0.3s ease, box-shadow 0.3s ease;
			height: 100%;
		}
		
		.book-card:hover {
			transform: translateY(-5px);
			box-shadow: 0 10px 25px rgba(0,0,0,0.15);
		}
		
		.book-image {
			position: relative;
			height: 250px;
			overflow: hidden;
		}
		
		.book-image img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		
		.book-status {
			position: absolute;
			top: 10px;
			right: 10px;
			padding: 5px 10px;
			border-radius: 15px;
			font-size: 0.8rem;
			font-weight: bold;
			text-transform: uppercase;
		}
		
		.book-status.available {
			background: #28a745;
			color: white;
		}
		
		.book-status.unavailable {
			background: #dc3545;
			color: white;
		}
		
		.book-content {
			padding: 20px;
		}
		
		.book-title {
			font-size: 1.3rem;
			font-weight: bold;
			margin-bottom: 10px;
			color: #333;
			line-height: 1.3;
		}
		
		.book-author {
			color: #666;
			font-style: italic;
			margin-bottom: 15px;
		}
		
		.book-details {
			margin-top: 15px;
		}
		
		.book-info {
			display: flex;
			justify-content: space-between;
			margin-bottom: 8px;
			font-size: 0.9rem;
		}
		
		.info-label {
			font-weight: bold;
			color: #555;
		}
		
		.info-value {
			color: #666;
		}
		
		.hero {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 100px 0;
			text-align: center;
		}
		
		.hero h1 {
			font-size: 3rem;
			margin-bottom: 20px;
		}
		
		.hero-desc {
			font-size: 1.2rem;
			opacity: 0.9;
		}
		
		.footer {
			background: #333;
			color: white;
			padding: 60px 0 20px;
		}
		
		.footer-title {
			font-size: 1.3rem;
			margin-bottom: 20px;
		}
		
		.footer-links {
			list-style: none;
			padding: 0;
		}
		
		.footer-links li {
			margin-bottom: 10px;
		}
		
		.footer-links a {
			color: #ccc;
			text-decoration: none;
			transition: color 0.3s ease;
		}
		
		.footer-links a:hover {
			color: white;
		}
		
		.contact-info p {
			margin-bottom: 10px;
			color: #ccc;
		}
		
		.footer-bottom {
			text-align: center;
			padding-top: 20px;
			border-top: 1px solid #555;
			margin-top: 40px;
		}
	</style>
</body>
</html>
