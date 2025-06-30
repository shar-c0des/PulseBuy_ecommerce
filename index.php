<?php
session_start()

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseBuy - South Africa's Leading C2C Marketplace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Sans', sans-serif;
        }

        :root {
            --primary-blue: #0050b5;
            --primary-orange: #ff6a00;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #495057;
            --text-dark: #212529;
            --white: #ffffff;
            --sale-red: #e63946;
            --accent-yellow: #ffd166;
        }

        body {
            background-color: #f0f2f5;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .top-nav {
            background-color: var(--primary-blue);
            color: var(--white);
            padding: 10px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo span {
            color: var(--accent-yellow);
        }

        .logo-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .brand-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            flex: 1;
            max-width: 700px;
            overflow-x: auto;
            padding-bottom: 3px;
        }

        .brand-nav::-webkit-scrollbar {
            height: 3px;
        }

        .brand-nav::-webkit-scrollbar-thumb {
            background-color: rgba(255,255,255,0.3);
            border-radius: 4px;
        }

        .brand-nav a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
            transition: opacity 0.3s;
            white-space: nowrap;
            padding: 4px 8px;
            border-radius: 3px;
            background: rgba(255,255,255,0.1);
        }

        .brand-nav a:hover {
            background: rgba(255,255,255,0.2);
        }

        .user-actions {            
            display: flex;
            gap: 15px;
            margin-left: auto;
            white-space: nowrap;
        }

        .user-actions a {
            color: var(--white);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: opacity 0.2s;
        }

        .user-actions a:hover {
            opacity: 0.8;
        }

        .user-actions i {
            font-size: 0.9rem;
        }

        .main-nav {
            background-color: var(--white);
            padding: 15px 5%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .nav-links {
            display: flex;
            gap: 22px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            position: relative;
            padding: 5px 0;
            transition: color 0.3s;
            white-space: nowrap;
        }

        .nav-links a:hover {
            color: var(--primary-blue);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary-blue);
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .search-box {
            display: flex;
            width: 450px;
            max-width: 100%;
            border: 2px solid var(--primary-blue);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,80,181,0.2);
        }

        .search-box input {
            flex: 1;
            border: none;
            padding: 12px 20px;
            font-size: 1rem;
            outline: none;
            background: var(--light-gray);
        }

        .search-box button {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            padding: 0 22px;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 600;
        }

        .search-box button:hover {
            background: #003d8a;
        }

        .category-nav {
            background: var(--white);
            padding: 15px 5%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            overflow-x: auto;
            gap: 25px;
            border-bottom: 1px solid var(--medium-gray);
        }

        .category-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-dark);
            min-width: 70px;
            transition: color 0.3s;
        }

        .category-item:hover {
            color: var(--primary-blue);
        }

        .category-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-blue);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            font-size: 1.2rem;
        }

        .category-name {
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
        }

        .main-container {
            display: flex;
            padding: 25px 5%;
            gap: 25px;
            max-width: 1600px;
            margin: 0 auto;
            flex-wrap: wrap;
        }

        .main-content {
            flex: 1;
            min-width: 0;
        }

        .hero-area {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 992px) {
            .hero-area {
                grid-template-columns: 1fr;
            }
        }

        .hero-carousel {
            background: var(--white);
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            height: 340px;
            position: relative;
        }

        .carousel-slide {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }

        .carousel-slide.active {
            opacity: 1;
        }

        .carousel-img {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .carousel-content {
            position: absolute;
            top: 40px;
            left: 40px;
            color: var(--text-dark);
            max-width: 50%;
            z-index: 2;
        }

        .carousel-content h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: var(--primary-blue);
            line-height: 1.2;
        }

        .carousel-content p {
            margin-bottom: 25px;
            font-size: 1rem;
            line-height: 1.5;
        }

        .carousel-link {
            display: inline-block;
            background: var(--primary-orange);
            color: var(--white);
            padding: 10px 25px;
            border-radius: 3px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }

        .carousel-link:hover {
            background: #e05b00;
            transform: translateY(-2px);
        }

        .carousel-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        .carousel-control {
            width: 38px;
            height: 38px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .carousel-control:hover {
            background: var(--white);
            transform: scale(1.1);
        }

        .carousel-pagination {
            position: absolute;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .pagination-dot {
            width: 10px;
            height: 10极;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .pagination-dot.active {
            background: var(--white);
            width: 28px;
            border-radius: 5px;
        }

        .promo-banners {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .promo-banner {
            background: var(--white);
            border-radius: 6px;
            overflow: hidden;
            height: 108px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            position: relative;
        }

        .promo-banner:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .promo-section {
            background: var(--white);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary-blue);
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary-orange);
        }

        .view-all {
            color: var(--dark-gray);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .view-all:hover {
            color: var(--primary-blue);
            text-decoration: underline;
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .promo-card {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            background: var(--white);
            position: relative;
        }

        .promo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .promo-img {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 700;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .promo-img::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.05);
        }

        .promo-content {
            padding: 15px;
            background: var(--white);
        }

        .promo-tag {
            display: inline-block;
            background: var(--primary-orange);
            color: var(--white);
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .promo-title {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
            line-height: 1.3;
            height: 40px;
            overflow: hidden;
        }

        .promo-price {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }

        .price-original {
            text-decoration: line-through;
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-right: 8px;
        }

        .promo-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #f39c12;
            margin-top: 8px;
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--sale-red);
            color: var(--white);
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.9rem;
            font-weight: 700;
            z-index: 2;
        }

        .flash-sale {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 15px;
            background: linear-gradient(to right, #e63946, #ff6b6b);
            border-radius: 6px;
            color: white;
            font-weight: 600;
        }

        .timer {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .timer-box {
            background: rgba(0,0,0,0.2);
            padding: 5px 10px;
            border-radius: 4px;
            min-width: 30px;
            text-align: center;
        }

        .feature-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .feature-box {
            background: var(--white);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
        }

        .feature-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .feature-icon {
            width: 45px;
            height: 45px;
            background: var(--primary-blue);
            color: var(--white);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.3rem;
        }

        .feature-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 6px;
            transition: all 0.3s;
            background: var(--light-gray);
            gap: 12px;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-dark);
        }

        .feature-item:hover {
            background: var(--medium-gray);
            transform: translateY(-3px);
        }

        .feature-item-img {
            width: 55px;
            height: 55px;
            background: var(--white);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-blue);
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .feature-item-content h4 {
            font-size: 0.95rem;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .feature-item-content p {
            color: var(--dark-gray);
            font-size: 0.85rem;
        }

        .sidebar {
            flex: 0 0 280px;
            min-width: 280px;
        }

        .user-card {
            background: var(--white);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
            text-align: center;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: var(--primary-blue);
        }

        .user-avatar {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            background: var(--light-gray);
            margin: 20px auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary-blue);
            border: 3px solid var(--medium-gray);
            position: relative;
        }

        .user-name {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .user-status {
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .user-points {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 6px;
            font-weight: 700;
            color: var(--primary-blue);
            font-size: 1rem;
        }

        .quick-links {
            background: var(--white);
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 25px;
        }

        .links-header {
            font-weight: 700;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--medium-gray);
            font-size: 1.1rem;
            color: var(--primary-blue);
        }

        .links-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .link-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px 10px;
            border-radius: 6px;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--text-dark);
            background: var(--light-gray);
        }

        .link-item:hover {
            background: var(--medium-gray);
            transform: translateY(-3px);
        }

        .link-icon {
            width: 45px;
            height: 45px;
            background: var(--primary-blue);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .link-text {
            font-size: 0.9rem;
            text-align: center;
            font-weight: 500;
        }

        .ad-banner {
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }

        .ad-banner img {
            width: 100%;
            display: block;
        }

        .footer {
            background: var(--white);
            padding: 40px 5% 20px;
            border-top: 2px solid var(--primary-blue);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-column h3 {
            color: var(--primary-blue);
            margin-bottom: 18px;
            font-size: 1.1rem;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--primary-blue);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: var(--dark-gray);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
            display: block;
            padding: 4px 0;
        }

        .footer-links a:hover {
            color: var(--primary-blue);
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: var(--primary-blue);
            color: white;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-links a:hover {
            transform: translateY(-3px);
            background: #003d8a;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--medium-gray);
            font-size: 0.9rem;
            color: var(--dark-gray);
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                display: flex;
                width: 100%;
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .quick-links, .ad-banner {
                flex: 1;
                min-width: calc(50% - 20px);
            }
        }

        @media (max-width: 992px) {
            .promo-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .feature-section {
                grid-template-columns: 1fr;
            }
            
            .hero-carousel {
                height: 300px;
            }
            
            .carousel-content {
                max-width: 60%;
                left: 30px;
            }
        }

        @media (max-width: 768px) {
            .top-nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .brand-nav {
                max-width: 100%;
                justify-content: flex-start;
                gap: 10px;
            }
            
            .user-actions {
                margin-left: 0;
            }
            
            .main-nav {
                justify-content: center;
            }
            
            .nav-links {
                justify-content: center;
                gap: 15px;
            }
            
            .search-box {
                width: 100%;
            }
            
            .carousel-content {
                max-width: 80%;
                top: 30px;
                left: 20px;
            }
            
            .promo-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .promo-grid {
                grid-template-columns: 1fr;
            }
            
            .carousel-content {
                position: relative;
                max-width: 100%;
                padding: 20px;
                text-align: center;
                top: 0;
                left: 0;
            }
            
            .carousel-link {
                display: block;
                margin: 0 auto;
                width: fit-content;
            }
            
            .carousel-controls {
                bottom: 15px;
                right: 15px;
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-area {
                margin-bottom: 15px;
            }
            
            .promo-banners {
                display: none;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .category-nav {
                padding: 12px 5%;
                gap: 15px;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .top-nav {
                padding: 10px 3%;
            }
            
            .logo-area {
                flex-direction: row;
            }
            
            .logo-subtitle {
                display: none;
            }
            
            .user-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .nav-links {
                gap: 10px;
            }
            
            .nav-links a {
                font-size: 0.85rem;
            }
            
            .carousel-content h2 {
                font-size: 1.4rem;
            }
            
            .carousel-content p {
                font-size: 0.95rem;
                margin-bottom: 15px;
            }
            
            .promo-card {
                max-width: 100%;
            }
            
            .sidebar > * {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <div class="logo-area">
            <div class="logo"><i class="fas fa-bolt"></i> Pulse<span>Buy</span></div>
            <div class="logo-subtitle">South Africa's #1 C2C Marketplace</div>
        </div>
        
        <div class="brand-nav">
            <a href="#">SUPER SALE</a>
            <a href="#">TODAY ONLY</a>
            <a href="#">NEW USERS</a>
            <a href="#">GIFT CARDS</a>
            <a href="#">FLASH DEALS</a>
            <a href="#">CASH BACK</a>
            <a href="#">FREE SHIPPING</a>
        </div>
        
        <div class="user-actions">
            <a href="loginSignup.php"><i class="fas fa-user"></i> Sign In</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Join </a>
            <a href="#"><i class="fas fa-headset"></i> Support</a>
        </div>
    </div>
    <nav class="main-nav">
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">Best Sellers</a>
            <a href="#">New Arrivals</a>
            <a href="#">Deals</a>
            <a href="#">Categories</a>
            <a href="#">Stores</a>
            <a href="#">Sell on PulseBuy</a>
        </div>
        
        <div class="search-box">
            <input type="text" placeholder="Search for anything...">
            <button><i class="fas fa-search"></i> Search</button>
        </div>
    </nav>
    <div class="category-nav">
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-mobile-alt"></i></div>
            <div class="category-name">Electronics</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-tshirt"></i></div>
            <div class="category-name">Fashion</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-home"></i></div>
            <div class="category-name">Home</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-heartbeat"></i></div>
            <div class="category-name">Beauty & Health</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-utensils"></i></div>
            <div class="category-name">Food</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-baby"></i></div>
            <div class="category-name">Baby & Kids</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-gamepad"></i></div>
            <div class="category-name">Toys & Games</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-car"></i></div>
            <div class="category-name">Motors</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-book"></i></div>
            <div class="category-name">Books</div>
        </a>
        <a href="#" class="category-item">
            <div class="category-icon"><i class="fas fa-couch"></i></div>
            <div class="category-name">Furniture</div>
        </a>
    </div>
    
    <!-- Flash Sale Timer -->
    <div class="flash-sale">
        <div>
            <i class="fas fa-bolt"></i> FLASH SALE - Ends in:
        </div>
        <div class="timer">
            <div class="timer-box">02</div>:
            <div class="timer-box">45</div>:
            <div class="timer-box">18</div>
        </div>
        <a href="#" class="view-all" style="color:white;">View All</a>
    </div>
    <div class="main-container">
        <div class="main-content">
            <div class="hero-area">
                <div class="hero-carousel">
                    <div class="carousel-slide active">
                        <div class="carousel-img" style="background: linear-gradient(135deg, #5d9cec, #8bb8f0);">
                            <div class="carousel-content">
                                <h2>Mega Tech Sale</h2>
                                <p>Save up to 60% on smartphones, laptops, and electronics. Limited time offer!</p>
                                <a href="#" class="carousel-link">Shop Now</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="carousel-slide">
                        <div class="carousel-img" style="background: linear-gradient(135deg, #ff9a9e, #fad0c4);">
                            <div class="carousel-content">
                                <h2>Summer Fashion Collection</h2>
                                <p>New arrivals for the hottest season from top South African designers</p>
                                <a href="#" class="carousel-link">Discover Styles</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="carousel-slide">
                        <div class="carousel-img" style="background: linear-gradient(135deg, #84fab0, #8fd3f4);">
                            <div class="carousel-content">
                                <h2>Home & Kitchen Deals</h2>
                                <p>Everything you need for your home at unbelievable prices</p>
                                <a href="#" class="carousel-link">Explore Deals</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="carousel-pagination">
                        <div class="pagination-dot active"></div>
                        <div class="pagination-dot"></div>
                        <div class="pagination-dot"></div>
                    </div>
                    <div class="carousel-controls">
                        <div class="carousel-control"><i class="fas fa-chevron-left"></i></div>
                        <div class="carousel-control"><i class="fas fa-chevron-right"></i></div>
                    </div>
                </div>
                
                <div class="promo-banners">
                    <div class="promo-banner" style="background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
                        <div style="padding: 15px; color: white;">
                            <h3 style="margin-bottom: 5px;">Free Shipping</h3>
                            <p>On orders over R500</p>
                        </div>
                    </div>
                    <div class="promo-banner" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                        <div style="padding: 15px; color: #333;">
                            <h3 style="margin-bottom: 5px;">Daily Deals</h3>
                            <p>New discounts every day</p>
                        </div>
                    </div>
                    <div class="promo-banner" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <div style="padding: 15px; color: white;">
                            <h3 style="margin-bottom: 5px;">PulseBuy Points</h3>
                            <p>Earn and redeem rewards</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daily Deals Section -->
            <div class="promo-section">
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-bolt"></i> Today's Best Deals</div>
                    <a href="#" class="view-all">View all</a>
                </div>
                <div class="promo-grid">
                    <div class="promo-card">
                        <div class="discount-badge">25% OFF</div>
                        <div class="promo-img" style="background: linear-gradient(135deg, #ff9a9e, #fad0c4);">
                            Smartphone
                        </div>
                        <div class="promo-content">
                            <span class="promo-tag">HOT</span>
                            <h3 class="promo-title">Samsung Galaxy S22</h3>
                            <div>
                                <span class="price-original">R14,999</span>
                                <span class="promo-price">R11,249</span>
                            </div>
                            <div class="promo-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                (128)
                            </div>
                        </div>
                    </div>
                    
                    <div class="promo-card">
                        <div class="discount-badge">35% OFF</div>
                        <div class="promo-img" style="background: linear-gradient(135deg, #a18cd1, #fbc2eb);">
                            Laptop
                        </div>
                        <div class="promo-content">
                            <span class="promo-tag">LIMITED</span>
                            <h3 class="promo-title">HP Pavilion Gaming Laptop</h3>
                            <div>
                                <span class="price-original">R18,999</span>
                                <span class="promo-price">R12,349</span>
                            </div>
                            <div class="promo-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                                (96)
                            </div>
                        </div>
                    </div>
                    
                    <div class="promo-card">
                        <div class="discount-badge">FREE</div>
                        <div class="promo-img" style="background: linear-gradient(135deg, #ffecd2, #fcb69f);">
                            Headphones
                        </div>
                        <div class="promo-content">
                            <span class="promo-tag">BUNDLE</span>
                            <h3 class="promo-title">Sony WH-1000XM4 Wireless</h3>
                            <div>
                                <span class="price-original">R4,299</span>
                                <span class="promo-price">R3,599</span>
                            </div>
                            <div class="promo-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                (245)
                            </div>
                        </div>
                    </div>
                    
                    <div class="promo-card">
                        <div class="discount-badge">50% OFF</div>
                        <div class="promo-img" style="background: linear-gradient(135deg, #84fab0, #8fd3f4);">
                            Smart Watch
                        </div>
                        <div class="promo-content">
                            <span class="promo-tag">NEW</span>
                            <h3 class="promo-title">Apple Watch Series 7</h3>
                            <div>
                                <span class="price-original">R8,999</span>
                                <span class="promo-price">R4,499</span>
                            </div>
                            <div class="promo-rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                (187)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Featured Categories -->
            <div class="feature-section">
                <div class="feature-box">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <div class="feature-title">Trending Categories</div>
                    </div>
                    <div class="feature-grid">
                        <a href="#" class="feature-item">
                            <div class="feature-item-img"><i class="fas fa-tshirt"></i></div>
                            <div class="feature-item-content">
                                <h4>Fashion</h4>
                                <p>Top clothing & accessories</p>
                            </div>
                        </a>
                        <a href="#" class="feature-item">
                            <div class="feature-item-img"><i class="fas fa-mobile"></i></div>
                            <div class="feature-item-content">
                                <h4>Electronics</h4>
                                <p>Phones, laptops & gadgets</p>
                            </div>
                        </a>
                        <a href="#" class="feature-item">
                            <div class="feature-item-img"><i class="fas fa-home"></i></div>
                            <div class="feature-item-content">
                                <h4>Home & Garden</h4>
                                <p>Furniture & decor</p>
                            </div>
                        </a>
                        <a href="#" class="feature-item">
                            <div class="feature-item-img"><i class="fas fa-car"></i></div>
                            <div class="feature-item-content">
                                <h4>Automotive</h4>
                                <p>Parts & accessories</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="feature-box">
                    <div class="feature-header">
                        <div class="feature-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="feature-title">Just For You</div>
                    </div>
                    <div class="feature-grid">
                        <a href="#" class="feature-item">
                            <div class="feature-item-img" style="background: #fff0f0; color: #e53935;">
                                <i class="fas fa-percent"></i>
                            </div>
                            <div class="feature-item-content">
                                <h4>Personalized Deals</h4>
                                <p>Special offers just for you</p>
                            </div>
                        </a>
                        <a href="#" class="feature-item">
                            <div class="feature-item-img" style="background: #e3f2fd; color: #0050b5;">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="feature-item-content">
                                <h4>Recently Viewed</h4>
                                <p>Continue browsing</p>
                            </div>
                        </a>
                        <a href="#" class="feature-item">
                            <div class="feature-item-img" style="background: #e8f5e9; color: #2e7d32;">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="feature-item-content">
                                <h4>Wishlist</h4>
                                <p>Save for later</p>
                            </div>
                        </a>
                        <a href="#" class="feature-item">
                            <div class="feature-item-img" style="background: #fff8e1; color: #ff8f00;">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="feature-item-content">
                                <h4>Top Rated</h4>
                                <p>Popular with buyers</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Local Sellers Section -->
            <div class="promo-section">
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-store"></i> Popular Local Sellers</div>
                    <a href="#" class="view-all">View all stores</a>
                </div>
                <div class="promo-grid">
                    <div class="promo-card">
                        <div class="promo-img" style="background: linear-gradient(135deg, #ffcc80, #ffa726);">
                            <div style="text-align: center; padding: 20px; color: #333;">
                                <h3>Cape Gadgets</h3>
                                <p>4.8 ★ (345)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="promo-card">
                        <div class="promo-img" style="background: linear-gradient(135deg, #80deea, #4dd0e1);">
                            <div style="text-align: center; padding: 20px; color: #333;">
                                <h3>Joburg Fashion</h3>
                                <p>4.6 ★ (287)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="promo-card">
                        <div class="promo-img" style="background: linear-gradient(135deg, #a5d6a7, #66bb6a);">
                            <div style="text-align: center; padding: 20px; color: #333;">
                                <h3>Durban Home</h3>
                                <p>4.7 ★ (412)</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="promo-card">
                        <div class="promo-img" style="background: linear-gradient(135deg, #f48fb1, #f06292);">
                            <div style="text-align: center; padding: 20px; color: #333;">
                                <h3>Pretoria Toys</h3>
                                <p>4.9 ★ (198)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-card">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-name">Welcome, Guest!</div>
                <div class="user-status">Sign in for better experience</div>
                <button style="width:100%; padding:10px; background:var(--primary-orange); color:white; border:none; border-radius:4px; margin-bottom:15px; font-weight:600; cursor:pointer;">
                    Sign In / Register
                </button>
                <div class="user-points">
                    <i class="fas fa-coins"></i> 0 PulseBuy Points
                </div>
            </div>
            
            <div class="quick-links">
                <div class="links-header">Quick Actions</div>
                <div class="links-grid">
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="link-text">My Cart</div>
                    </a>
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="link-text">Wishlist</div>
                    </a>
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="link-text">Orders</div>
                    </a>
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="link-text">Messages</div>
                    </a>
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="link-text">My Store</div>
                    </a>
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="link-text">Coupons</div>
                    </a>
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="link-text">History</div>
                    </a>
                    <a href="#" class="link-item">
                        <div class="link-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="link-text">Settings</div>
                    </a>
                </div>
            </div>
            
            <div class="ad-banner">
                <div style="padding:20px; text-align:center; background:#e3f2fd;">
                    <h3 style="color:var(--primary-blue);">Sell on PulseBuy</h3>
                    <p>Start your own store today</p>
                    <button style="padding:8px 20px; background:var(--primary-orange); color:white; border:none; border-radius:4px; font-weight:600; cursor:pointer; margin-top:10px;">
                        Become a Seller
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Customer Service</h3>
                <ul class="footer-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">Returns & Refunds</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>About PulseBuy</h3>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">PulseBuy Cares</a></li>
                    <li><a href="#">Terms & Privacy</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Payment Methods</h3>
                <ul class="footer-links">
                    <li><a href="#">Credit/Debit Cards</a></li>
                    <li><a href="#">EFT Payments</a></li>
                    <li><a href="#">PulseBuy Wallet</a></li>
                    <li><a href="#">Cash on Delivery</a></li>
                    <li><a href="#">Payment Security</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Connect With Us</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-phone"></i> 0861 PULSEBUY (785732)</a></li>
                    <li><a href="#"><i class="fas fa-envelope"></i> support@pulsebuy.co.za</a></li>
                    <li>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="copyright">
            © 2023 PulseBuy (Pty) Ltd. South Africa's #1 C2C Marketplace. All Rights Reserved.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.carousel-slide');
            const dots = document.querySelectorAll('.pagination-dot');
            const nextBtn = document.querySelector('.fa-chevron-right').closest('.carousel-control');
            const prevBtn = document.querySelector('.fa-chevron-left').closest('.carousel-control');
            
            let currentSlide = 0;
            const slideCount = slides.length;
            
            function showSlide(index) {
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));
                
                slides[index].classList.add('active');
                dots[index].classList.add('active');
                dots[index].style.width = '28px';
                dots[index].style.borderRadius = '5px';
                dots.forEach((dot, i) => {
                    if(i !== index) {
                        dot.style.width = '10px';
                        dot.style.borderRadius = '50%';
                    }
                });
                
                currentSlide = index;
            }
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => showSlide(index));
            });
            
            nextBtn.addEventListener('click', () => {
                let next = (currentSlide + 1) % slideCount;
                showSlide(next);
            });
            
            prevBtn.addEventListener('click', () => {
                let prev = (currentSlide - 1 + slideCount) % slideCount;
                showSlide(prev);
            });
            let carouselInterval = setInterval(() => {
                let next = (currentSlide + 1) % slideCount;
                showSlide(next);
            }, 5000);
            
            const carousel = document.querySelector('.hero-carousel');
            carousel.addEventListener('mouseenter', () => clearInterval(carouselInterval));
            carousel.addEventListener('mouseleave', () => {
                carouselInterval = setInterval(() => {
                    let next = (currentSlide + 1) % slideCount;
                    showSlide(next);
                }, 5000);
            });
            const promoCards = document.querySelectorAll('.promo-card, .feature-item, .link-item');
            promoCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if(this.classList.contains('promo-card')) {
                        const title = this.querySelector('.promo-title').textContent;
                        alert(`Viewing product: "${title}"`);
                    } else if(this.classList.contains('feature-item')) {
                        const title = this.querySelector('h4').textContent;
                        alert(`Navigating to: ${title}`);
                    } else if(this.classList.contains('link-item')) {
                        const text = this.querySelector('.link-text').textContent;
                        alert(`Opening: ${text}`);
                    }
                });
            });
            const searchBox = document.querySelector('.search-box');
            const searchInput = searchBox.querySelector('input');
            const searchButton = searchBox.querySelector('button');
            
            searchInput.addEventListener('focus', function() {
                searchBox.style.boxShadow = '0 4px 12px rgba(0, 80, 181, 0.25)';
            });
            
            searchInput.addEventListener('blur', function() {
                searchBox.style.boxShadow = '0 2px 5px rgba(0,80,181,0.2)';
            });
            
            searchButton.addEventListener('click', function(e) {
                e.preventDefault();
                if(searchInput.value.trim() !== '') {
                    alert(`Searching for: "${searchInput.value}"`);
                }
            });
            
            searchInput.addEventListener('keypress', function(e) {
                if(e.key === 'Enter') {
                    e.preventDefault();
                    searchButton.click();
                }
            });
        
            const signInButton = document.querySelector('.user-card button');
            signInButton.addEventListener('click', function() {
                alert('Opening sign in/registration modal');
            });
        });
    </script>
</body>
</html>

<div class="user-actions">
    <a href="public/login.php">
        <i class="fas fa-sign-in-alt"></i>
        Login
    </a>
    <a href="#"><i class="fas fa-user"></i> Sign In</a>
    <a href="#"><i class="fas fa-user-plus"></i> Join</a>
    <a href="#"><i class="fas fa-headset"></i> Support</a>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</

