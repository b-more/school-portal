<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="St. Francis of Assisi Primary School - Faith, Family, Future">

        <title>St. Francis of Assisi Primary School</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Fontawesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <!-- Styles -->
        <style>
            /* Base styles */
            *,:before,:after{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}:before,:after{--tw-content: ""}html,:host{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;-o-tab-size:4;tab-size:4;font-family:Figtree,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji",Segoe UI Symbol,"Noto Color Emoji";font-feature-settings:normal;font-variation-settings:normal;-webkit-tap-highlight-color:transparent}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,samp,pre{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,Courier New,monospace;font-feature-settings:normal;font-variation-settings:normal;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-size:100%;font-weight:inherit;line-height:inherit;letter-spacing:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}button,input:where([type=button]),input:where([type=reset]),input:where([type=submit]){-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dl,dd,h1,h2,h3,h4,h5,h6,hr,figure,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}ol,ul,menu{list-style:none;margin:0;padding:0}dialog{padding:0}textarea{resize:vertical}input::-moz-placeholder,textarea::-moz-placeholder{opacity:1;color:#9ca3af}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}button,[role=button]{cursor:pointer}:disabled{cursor:default}img,svg,video,canvas,audio,iframe,embed,object{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]:where(:not([hidden=until-found])){display:none}

            /* Utility Classes */
            .container { width: 100%; max-width: 1280px; margin-left: auto; margin-right: auto; padding-left: 1rem; padding-right: 1rem; }
            .flex { display: flex; }
            .items-center { align-items: center; }
            .justify-between { justify-content: space-between; }
            .justify-center { justify-content: center; }
            .flex-col { flex-direction: column; }
            .text-center { text-align: center; }
            .relative { position: relative; }
            .absolute { position: absolute; }
            .fixed { position: fixed; }
            .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
            .z-10 { z-index: 10; }
            .z-20 { z-index: 20; }
            .z-50 { z-index: 50; }
            .h-screen { height: 100vh; }
            .w-full { width: 100%; }
            .max-w-3xl { max-width: 48rem; }
            .max-w-7xl { max-width: 80rem; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .mt-4 { margin-top: 1rem; }
            .mt-6 { margin-top: 1.5rem; }
            .mt-8 { margin-top: 2rem; }
            .mt-10 { margin-top: 2.5rem; }
            .mb-2 { margin-bottom: 0.5rem; }
            .mb-4 { margin-bottom: 1rem; }
            .mb-6 { margin-bottom: 1.5rem; }
            .mb-8 { margin-bottom: 2rem; }
            .mb-10 { margin-bottom: 2.5rem; }
            .p-4 { padding: 1rem; }
            .p-6 { padding: 1.5rem; }
            .p-8 { padding: 2rem; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
            .px-8 { padding-left: 2rem; padding-right: 2rem; }
            .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
            .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
            .py-12 { padding-top: 3rem; padding-bottom: 3rem; }
            .py-16 { padding-top: 4rem; padding-bottom: 4rem; }
            .py-24 { padding-top: 6rem; padding-bottom: 6rem; }
            .pt-16 { padding-top: 4rem; }
            .rounded-md { border-radius: 0.375rem; }
            .rounded-lg { border-radius: 0.5rem; }
            .rounded-full { border-radius: 9999px; }
            .border { border-width: 1px; }
            .border-t { border-top-width: 1px; }
            .border-b { border-bottom-width: 1px; }
            .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
            .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
            .grid { display: grid; }
            .gap-4 { gap: 1rem; }
            .gap-6 { gap: 1.5rem; }
            .gap-8 { gap: 2rem; }
            .gap-10 { gap: 2.5rem; }
            .object-cover { object-fit: cover; }
            .font-medium { font-weight: 500; }
            .font-semibold { font-weight: 600; }
            .font-bold { font-weight: 700; }
            .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
            .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
            .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
            .text-2xl { font-size: 1.5rem; line-height: 2rem; }
            .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
            .text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
            .text-5xl { font-size: 3rem; line-height: 1; }
            .text-6xl { font-size: 3.75rem; line-height: 1; }
            .transition { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
            .transition-colors { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
            .transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
            .duration-300 { transition-duration: 300ms; }
            .duration-500 { transition-duration: 500ms; }
            .hover\:scale-110:hover { transform: scale(1.1); }
            .hover\:underline:hover { text-decoration-line: underline; }
            .hover\:bg-opacity-90:hover { --tw-bg-opacity: 0.9; }

            /* Navy Blue Theme */
            :root {
                --primary-navy: #001F54; /* Dark navy as main color */
                --secondary-navy: #083D77; /* Medium navy blue */
                --light-blue: #2D61A6; /* Lighter blue accent */
                --accent-red: #D50032; /* Red from logo - for CTAs and accents */
                --dark-navy: #00142E; /* Darker shade for depth */

                /* Text Colors */
                --text-dark: #111827;
                --text-medium: #4B5563;
                --text-light: #F9FAFB;

                /* Background Colors */
                --bg-light: #F9FAFB;
                --bg-light-blue: #EEF2FF;
                --bg-white: #FFFFFF;

                /* UI Element Colors */
                --cta-hover: #B8002B; /* Darker red for CTA hover */
                --cta-active: #9E0025; /* Even darker red for active state */
                --navy-highlight: #E5EDFF; /* Very light blue for highlights */
            }

            body {
                font-family: 'Figtree', sans-serif;
                color: var(--text-dark);
                scroll-behavior: smooth;
            }

            /* Navbar */
            .navbar {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1000;
                background-color: rgba(0, 31, 84, 0.9); /* Navy with transparency */
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                color: white;
                transition: all 0.3s ease;
            }

            .navbar.scrolled {
                background-color: rgba(0, 31, 84, 0.95);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            }

            /* Hero Section */
            .hero {
                position: relative;
                height: 100vh;
                width: 100%;
                overflow: hidden;
            }

            .hero-slide {
                position: absolute;
                inset: 0;
                opacity: 0;
                transition: opacity 1s ease-in-out;
                background-size: cover;
                background-position: center;
            }

            .hero-slide.active {
                opacity: 1;
            }

            .hero-overlay {
                position: absolute;
                inset: 0;
                background: linear-gradient(to right, rgba(0, 31, 84, 0.9), rgba(8, 61, 119, 0.8));
            }

            .hero-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                /* mix-blend-mode: overlay; */
                object-position: center;
                max-height: 100vh;
            }

            .hero-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                color: white;
                z-index: 10;
                width: 80%;
                max-width: 800px;
            }

            /* Buttons */
            .btn-primary {
                display: inline-block;
                background-color: var(--accent-red);
                color: white;
                padding: 0.75rem 1.75rem;
                border-radius: 9999px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .btn-primary2 {
                display: inline-block;
                background-color: var(--accent-red);
                color: white;
                padding: 0.75rem 1.75rem;
                border-radius: 9999px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .btn-primary:hover {
                background-color: var(--cta-hover);
                transform: translateY(-2px);
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            }

            /* Section Titles */
            .section-title {
                position: relative;
                padding-bottom: 1rem;
                margin-bottom: 2rem;
                color: var(--primary-navy);
                font-weight: 700;
            }

            .section-title:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 80px;
                height: 3px;
                background: linear-gradient(to right, var(--primary-navy), var(--accent-red));
            }

            /* Feature Cards */
            .feature-card {
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                transition: all 0.3s ease;
                border-bottom: 3px solid var(--primary-navy);
                background-color: white;
            }

            .feature-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                border-bottom-color: var(--accent-red);
            }

            /* Icon Circles */
            .icon-circle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 3.5rem;
                height: 3.5rem;
                border-radius: 9999px;
                background-color: var(--navy-highlight);
                color: var(--primary-navy);
                margin-bottom: 1rem;
            }

            /* Testimonial Cards */
            .testimonial-card {
                background-color: white;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                padding: 1.5rem;
                border-top: 3px solid var(--primary-navy);
                transition: all 0.3s ease;
            }

            .testimonial-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            }

            /* Stats Boxes */
            .stats-box {
                background-color: var(--navy-highlight);
                border-radius: 0.5rem;
                padding: 1.5rem;
                text-align: center;
            }

            .stats-number {
                font-size: 2.5rem;
                font-weight: 700;
                color: var(--accent-red);
                margin-bottom: 0.5rem;
            }

            /* Footer */
            .footer {
                background-color: var(--primary-navy);
                color: white;
                padding: 3rem 0;
            }

            .footer-title {
                font-size: 1.25rem;
                font-weight: 700;
                margin-bottom: 1.25rem;
                position: relative;
                padding-bottom: 0.75rem;
            }

            .footer-title:after {
                content: '';
                position: absolute;
                left: 0;
                bottom: 0;
                width: 50px;
                height: 2px;
                background-color: var(--accent-red);
            }

            .footer-link {
                color: rgba(255, 255, 255, 0.7);
                transition: all 0.2s ease;
                display: block;
                margin-bottom: 0.5rem;
            }

            .footer-link:hover {
                color: white;
                padding-left: 0.5rem;
            }

            /* Social Icons */
            .social-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 9999px;
                background-color: rgba(255, 255, 255, 0.1);
                color: white;
                transition: all 0.3s ease;
                margin-right: 0.5rem;
            }

            .social-icon:hover {
                background-color: var(--accent-red);
                transform: translateY(-3px);
            }

            /* Mobile Menu */
            #mobile-menu {
                position: fixed;
                top: 72px;
                right: -100%;
                width: 80%;
                height: calc(100vh - 72px);
                background-color: var(--primary-navy);
                transition: right 0.3s ease;
                z-index: 40;
                padding: 2rem;
                overflow-y: auto;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
                color: white !important;
            }

            #mobile-menu.active {
                right: 0;
            }

            #enroll a {
                transform: translateY(0);
                transition: all 0.3s ease;
            }

            #enroll a:hover {
                transform: translateY(-3px);
            }

            #enroll a:active {
                transform: translateY(0);
            }

            /* Ensure text is white in this section */
            #enroll, #enroll h2, #enroll p {
                color: white !important;
            }

            /* Match the navy text color with the school brand */
            .text-navy {
                color: var(--primary-navy);
            }

            /* Media Queries */
            @media (min-width: 768px) {
                .md\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .md\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .md\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
                .md\:flex { display: flex; }
                .md\:hidden { display: none; }
                .md\:text-xl { font-size: 1.25rem; line-height: 1.75rem; }
                .md\:text-2xl { font-size: 1.5rem; line-height: 2rem; }
                .md\:text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
                .md\:text-5xl { font-size: 3rem; line-height: 1; }
                .md\:flex-row { flex-direction: row; }
                .md\:py-24 { padding-top: 6rem; padding-bottom: 6rem; }
                .md\:mt-0 { margin-top: 0; }
            }

            @media (min-width: 1024px) {
                .lg\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .lg\:text-6xl { font-size: 3.75rem; line-height: 1; }
            }

            @media (max-width: 767px) {
                .hero-content h1 {
                    font-size: 2rem;
                }

                .section-title {
                    font-size: 1.75rem;
                }

                .hidden { display: none; }
            }
            @media (min-width: 768px) and (max-width: 1023px) {
                .navbar a {
                    padding-left: 10px;
                    padding-right: 10px;
                }

                .navbar .btn-primary,
                .navbar [href="#enroll"] {
                    padding-left: 12px;
                    padding-right: 12px;
                    font-size: 0.875rem;
                }
            }
        </style>
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="/" class="flex items-center gap-3">
                    <!-- Fixed image size -->
                    <img src="{{ asset('imgz/logo.png') }}" alt="St. Francis Logo" class="w-20 h-20 rounded-full border-2 border-white/30 object-cover" style="max-width: 40px; max-height: 40px;">
                    <div>
                        {{-- <span class="text-xl font-bold text-white block leading-tight">St. Francis of Assisi</span> --}}
                        {{-- <span class="text-xs text-white/80 block">Faith, Family, Future</span> --}}
                    </div>
                </a>

                <!-- Added more spacing between items -->
                <div class="hidden md:flex items-center">
                    <a href="#about" class="text-white hover:text-white/80 transition-colors px-4">About Us</a>
                    <a href="#programs" class="text-white hover:text-white/80 transition-colors px-4">Programs</a>
                    <a href="#features" class="text-white hover:text-white/80 transition-colors px-4">Why Choose Us</a>
                    <a href="#testimonials" class="text-white hover:text-white/80 transition-colors px-4">Testimonials</a>
                    <a href="#contact" class="text-white hover:text-white/80 transition-colors px-4">Contact</a>
                    <!-- Added more space between icon and text -->
                    <a href="/admin" class="btn-primary2">
                        <i class="fas fa-lock" style="margin-right: 8px;"></i>Staff Login
                    </a>
                </div>

                <button class="md:hidden text-white focus:outline-none" id="menu-toggle">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </nav>

        <!-- Updated Mobile Menu with White Text -->
        <div id="mobile-menu" style="text-align: left;">
            <div class="flex flex-col space-y-6">
                <a href="#about" class="text-lg text-white hover:text-white/80 transition-colors">About Us</a>
                <a href="#programs" class="text-lg text-white hover:text-white/80 transition-colors">Programs</a>
                <a href="#features" class="text-lg text-white hover:text-white/80 transition-colors">Why Choose Us</a>
                <a href="#testimonials" class="text-lg text-white hover:text-white/80 transition-colors">Testimonials</a>
                <a href="#contact" class="text-lg text-white hover:text-white/80 transition-colors">Contact</a>
                <!-- Added more space between icon and text in mobile menu -->
                <a href="/admin" class="inline-block bg-[#D50032] text-white px-4 py-2 rounded-full text-sm font-semibold hover:bg-[#B8002B] transition-colors shadow-md text-center">
                    <i class="fas fa-lock" style="margin-right: 8px;"></i>Staff Login
                </a>
            </div>
        </div>


        <!-- Hero Section -->
        <section id="home" class="hero pt-16">
            <div class="hero-slide active">
                <div class="hero-overlay"></div>
                <img src="{{ asset('imgz/1.jpg') }}" alt="School Campus" class="hero-image" onerror="this.src='https://via.placeholder.com/1920x1080'">
                <div class="hero-content">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6">
                        Welcome to St. Francis of Assisi Primary School
                    </h1>
                    <p class="text-xl md:text-2xl text-white/90 mb-8">
                        Faith, Family, Future
                    </p>
                    <a href="#enroll" class="btn-primary">Enroll Your Child Today</a>
                </div>
            </div>

            <div class="hero-slide">
                <div class="hero-overlay"></div>
                <img src="{{ asset('imgz/2.jpg') }}" alt="Students Learning" class="hero-image" onerror="this.src='https://via.placeholder.com/1920x1080?text=Students+Learning'">
                <div class="hero-content">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6">
                        Excellence in Education
                    </h1>
                    <p class="text-xl md:text-2xl text-white/90 mb-8">
                        Providing quality education for over 20 years
                    </p>
                    <a href="#programs" class="btn-primary">Discover Our Programs</a>
                </div>
            </div>

            <div class="hero-slide">
                <div class="hero-overlay"></div>
                <img src="{{ asset('imgz/3.jpg') }}" alt="School Activities" class="hero-image" onerror="this.src='https://via.placeholder.com/1920x1080?text=School+Activities'">
                <div class="hero-content">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6">
                        Holistic Development
                    </h1>
                    <p class="text-xl md:text-2xl text-white/90 mb-8">
                        Building character, knowledge, and skills
                    </p>
                    <a href="#features" class="btn-primary">Why Choose Us</a>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-16 md:py-24 px-6 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center section-title">About Our School</h2>

                <div class="grid md:grid-cols-2 gap-10 items-center">
                    <div>
                        <img src="{{ asset('imgz/18.jpg') }}" alt="School Building" class="rounded-lg shadow-lg">
                    </div>

                    <div>
                        <h3 class="text-2xl font-bold mb-4" style="color: var(--primary-navy);">A Legacy of Excellence</h3>
                        <p class="text-gray-600 mb-6">
                            St. Francis of Assisi Primary School has been a pillar of educational excellence since its founding.
                            We provide a nurturing environment where children can grow academically, socially, and spiritually.
                        </p>

                        {{-- <h3 class="text-2xl font-bold mb-4" style="color: var(--primary-navy);">Our Mission</h3>
                        <p class="text-gray-600 mb-6">
                            To inspire and empower each student to achieve academic excellence, personal growth, and social responsibility
                            within a safe, supportive, and challenging learning environment based on Catholic values.
                        </p> --}}

                        <div class="grid grid-cols-2 gap-4">
                            <div class="stats-box">
                                <div class="stats-number">200+</div>
                                <span class="text-gray-600">Students</span>
                            </div>

                            <div class="stats-box">
                                <div class="stats-number">15+</div>
                                <span class="text-gray-600">Qualified Teachers</span>
                            </div>

                            {{-- <div class="stats-box">
                                <div class="stats-number">20+</div>
                                <span class="text-gray-600">Years of Excellence</span>
                            </div> --}}

                            <div class="stats-box">
                                <div class="stats-number">15+</div>
                                <span class="text-gray-600">Programs & Activities</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Programs Section -->
        <section id="programs" class="py-16 md:py-24 px-6 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center section-title">Our Academic Programs</h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="feature-card">
                        <img src="{{ asset('imgz/4.jpg') }}" alt="Early Childhood" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Early Childhood Program</h3>
                            <p class="text-gray-600 mb-4">
                                Designed for ages 3-5, our early childhood program focuses on developmental milestones
                                through play-based learning and foundational skill building.
                            </p>
                            <a href="#" style="color: var(--accent-red);" class="font-medium hover:underline">Learn More →</a>
                        </div>
                    </div>

                    <div class="feature-card">
                        <img src="{{ asset('imgz/14.jpg') }}" alt="Elementary Education" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Elementary Education</h3>
                            <p class="text-gray-600 mb-4">
                                Our comprehensive curriculum for grades 1-5 builds strong academic foundations in literacy,
                                mathematics, science, social studies, and the arts.
                            </p>
                            <a href="#" style="color: var(--accent-red);" class="font-medium hover:underline">Learn More →</a>
                        </div>
                    </div>

                    <div class="feature-card">
                        <img src="{{ asset('imgz/5.jpg') }}" alt="Special Programs" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Enrichment Programs</h3>
                            <p class="text-gray-600 mb-4">
                                We offer various enrichment programs including music, art, physical education,
                                technology, and foreign language to develop well-rounded students.
                            </p>
                            <a href="#" style="color: var(--accent-red);" class="font-medium hover:underline">Learn More →</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section id="features" class="py-16 md:py-24 px-6 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center section-title">Why Choose St. Francis?</h2>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 rounded-lg transition-all duration-300 hover:shadow-lg" style="background-color: var(--bg-light-blue);">
                        <div class="icon-circle">
                            <i class="fas fa-graduation-cap text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Academic Excellence</h3>
                        <p class="text-gray-600">
                            Our rigorous curriculum and dedicated teachers ensure that students achieve
                            high academic standards and develop a love for learning.
                        </p>
                    </div>

                    <div class="p-6 rounded-lg transition-all duration-300 hover:shadow-lg" style="background-color: var(--bg-light-blue);">
                        <div class="icon-circle">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Small Class Sizes</h3>
                        <p class="text-gray-600">
                            With limited enrollment, we provide personalized attention to each child,
                            ensuring they receive the support they need to thrive.
                        </p>
                    </div>

                    <div class="p-6 rounded-lg transition-all duration-300 hover:shadow-lg" style="background-color: var(--bg-light-blue);">
                        <div class="icon-circle">
                            <i class="fas fa-globe text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Holistic Education</h3>
                        <p class="text-gray-600">
                            We focus on developing the whole child – intellectually, physically, emotionally,
                            and spiritually – preparing them for lifelong success.
                        </p>
                    </div>

                    <div class="p-6 rounded-lg transition-all duration-300 hover:shadow-lg" style="background-color: var(--bg-light-blue);">
                        <div class="icon-circle">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Safe Environment</h3>
                        <p class="text-gray-600">
                            Safety is our priority. Our secure campus and caring staff ensure that
                            children learn in a protected and nurturing environment.
                        </p>
                    </div>

                    <div class="p-6 rounded-lg transition-all duration-300 hover:shadow-lg" style="background-color: var(--bg-light-blue);">
                        <div class="icon-circle">
                            <i class="fas fa-laptop text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Modern Facilities</h3>
                        <p class="text-gray-600">
                            Our school features state-of-the-art classrooms, library, computer lab,
                            science lab, art studio, and recreational areas.
                        </p>
                    </div>

                    <div class="p-6 rounded-lg transition-all duration-300 hover:shadow-lg" style="background-color: var(--bg-light-blue);">
                        <div class="icon-circle">
                            <i class="fas fa-hands-helping text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-3" style="color: var(--primary-navy);">Community Involvement</h3>
                        <p class="text-gray-600">
                            We foster strong partnerships between parents, teachers, and the community
                            to provide the best possible education for our students.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-16 md:py-24 px-6 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center section-title">What Parents Say</h2>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="testimonial-card">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4 italic">
                            "St. Francis has provided my daughter with an exceptional education. The teachers
                            are caring and dedicated, and the school community feels like family."
                        </p>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full mr-3 flex items-center justify-center text-white" style="background-color: var(--primary-navy);">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="font-bold">Sarah Thompson</p>
                                <p class="text-sm text-gray-500">Parent of Grade 3 Student</p>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-card">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4 italic">
                            "The individual attention my son receives at St. Francis has helped him excel
                            academically and grow in confidence. We couldn't be happier with our choice."
                        </p>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full mr-3 flex items-center justify-center text-white" style="background-color: var(--primary-navy);">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="font-bold">Michael Johnson</p>
                                <p class="text-sm text-gray-500">Parent of Grade 2 Student</p>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-card">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4 italic">
                            "The values-based education at St. Francis has positively shaped my children's character.
                            The school's commitment to excellence is evident in everything they do."
                        </p>
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full mr-3 flex items-center justify-center text-white" style="background-color: var(--primary-navy);">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="font-bold">Amanda Rodriguez</p>
                                <p class="text-sm text-gray-500">Parent of Grade 1 & 4 Students</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="py-16 md:py-24 px-6 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center section-title">School Life</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/6.jpg') }}" alt="Sports" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/7.jpg') }}" alt="Sports" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/8.jpg') }}" alt="Art Class" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/9.jpg') }}" alt="Library" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/10.jpg') }}" alt="Computer Lab" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/11.jpg') }}" alt="Music" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/12.jpg') }}" alt="Science Lab" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                    <div class="overflow-hidden rounded-lg">
                        <img src="{{ asset('imgz/13.jpg') }}" alt="Playground" class="w-full h-48 object-cover transition-transform duration-500 hover:scale-110">
                    </div>
                </div>
            </div>
        </section>

        <!-- Enrollment CTA Section -->
        <section id="enroll" class="py-16 md:py-24 px-6 text-white" style="background-color: var(--primary-navy);">
            <div class="max-w-7xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6 text-white">Enroll Your Child Today</h2>
                <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto text-white opacity-90">
                    Join our vibrant learning community and give your child the gift of an exceptional education.
                </p>
                <div class="flex flex-col md:flex-row gap-4 justify-center">
                    <!-- First button - styled to match school colors -->
                    <a href="#" class="bg-accent-red border-2 border-accent-red px-6 py-3 rounded-full font-semibold text-base text-white transition-all duration-300 hover:bg-transparent hover:border-white shadow-lg flex items-center justify-center" style="background-color: var(--accent-red); border-color: var(--accent-red);">
                        <i class="fas fa-download mr-2 px-4"></i>
                        Download Application
                    </a>

                    <!-- Second button - accent red button -->
                    <a href="#" class="bg-accent-red border-2 border-accent-red px-6 py-3 rounded-full font-semibold text-base text-white transition-all duration-300 hover:bg-transparent hover:border-white shadow-lg flex items-center justify-center" style="background-color: var(--accent-red); border-color: var(--accent-red);">
                        <i class="fas fa-calendar-check mr-2 px-4"></i>
                        Schedule a Tour
                    </a>
                </div>
            </div>
        </section>


        <!-- Contact Section -->
        <section id="contact" class="py-16 md:py-24 px-6 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-center section-title">Contact Us</h2>

                <div class="grid md:grid-cols-2 gap-10">
                    <div>
                        <h3 class="text-2xl font-bold mb-6" style="color: var(--primary-navy);">Get in Touch</h3>

                        <form class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label for="name" class="block text-gray-700 mb-2">Full Name</label>
                                    <input type="text" id="name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-900">
                                </div>
                                <div>
                                    <label for="email" class="block text-gray-700 mb-2">Email Address</label>
                                    <input type="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-900">
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-gray-700 mb-2">Subject</label>
                                <input type="text" id="subject" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-900">
                            </div>

                            <div>
                                <label for="message" class="block text-gray-700 mb-2">Message</label>
                                <textarea id="message" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-900"></textarea>
                            </div>

                            <button type="submit" class="btn-primary">Send Message</button>
                        </form>
                    </div>

                    <div>
                        <h3 class="text-2xl font-bold mb-6" style="color: var(--primary-navy);">School Information</h3>

                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-16 h-10 rounded-full flex items-center justify-center mt-1" style="background-color: var(--navy-highlight);">
                                    <i class="fas fa-map-marker-alt" style="color: var(--primary-navy);"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-1">Address</h4>
                                    <p class="text-gray-600">123 School Street, City Name, State 12345</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-20 h-10 rounded-full flex items-center justify-center mr-6 mt-1" style="background-color: var(--navy-highlight);">
                                    <i class="fas fa-phone" style="color: var(--primary-navy);"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-0">Phone</h4>
                                    <p class="text-gray-600">(123) 456-7890</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-12 h-10 rounded-full flex items-center justify-center mr-4 mt-1" style="background-color: var(--navy-highlight);">
                                    <i class="fas fa-envelope" style="color: var(--primary-navy);"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-1">Email</h4>
                                    <p class="text-gray-600">info@stfrancisschool.org</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-4 mt-1" style="background-color: var(--navy-highlight);">
                                    <i class="fas fa-clock" style="color: var(--primary-navy);"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 mb-1">Office Hours</h4>
                                    <p class="text-gray-600">Monday - Friday: 8:00 AM - 4:00 PM</p>
                                    <p class="text-gray-600">Saturday & Sunday: Closed</p>
                                </div>
                            </div>
                        </div>

                        </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer py-12">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="footer-title">St. Francis of Assisi</h3>
                        <p class="text-gray-300 mb-4">Faith, Family, Future since 2000.</p>
                        <div class="flex space-x-4">
                            <a href="#" class="social-icon">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-icon">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-icon">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-icon">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>

                    <div>
                        <h3 class="footer-title">Quick Links</h3>
                        <ul>
                            <li><a href="#about" class="footer-link">About Us</a></li>
                            <li><a href="#programs" class="footer-link">Programs</a></li>
                            <li><a href="#features" class="footer-link">Why Choose Us</a></li>
                            <li><a href="#testimonials" class="footer-link">Testimonials</a></li>
                            <li><a href="#contact" class="footer-link">Contact</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="footer-title">Resources</h3>
                        <ul>
                            <li><a href="#" class="footer-link">Parent Portal</a></li>
                            <li><a href="#" class="footer-link">Academic Calendar</a></li>
                            <li><a href="#" class="footer-link">School Policies</a></li>
                            <li><a href="#" class="footer-link">Curriculum</a></li>
                            <li><a href="#" class="footer-link">Staff Directory</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="footer-title">Subscribe to Newsletter</h3>
                        <p class="text-gray-300 mb-4">Stay updated with school news and events.</p>
                        <form class="flex">
                            <input type="email" placeholder="Your email" class="px-4 py-2 rounded-l-md w-full focus:outline-none text-gray-800">
                            <button type="submit" class="px-4 py-2 rounded-r-md transition hover:bg-opacity-90" style="background-color: var(--accent-red);">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-10 pt-6 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-300">© 2025 St. Francis of Assisi Primary School. All rights reserved.</p>
                    <div class="mt-4 md:mt-0">
                        <a href="#" class="text-gray-300 hover:text-white transition mr-4">Privacy Policy</a>
                        <a href="#" class="text-gray-300 hover:text-white transition">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- JavaScript for Interactive Features -->
        {{-- <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Navbar scroll effect
                const navbar = document.querySelector('.navbar');

                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                });

                // Mobile menu toggle
                const menuToggle = document.getElementById('menu-toggle');
                const mobileMenu = document.getElementById('mobile-menu');

                menuToggle.addEventListener('click', function() {
                    if (mobileMenu.style.right === '0px') {
                        mobileMenu.style.right = '-100%';
                        // Change icon
                        this.innerHTML = '<i class="fas fa-bars text-2xl"></i>';
                    } else {
                        mobileMenu.style.right = '0px';
                        // Change icon
                        this.innerHTML = '<i class="fas fa-times text-2xl"></i>';
                    }
                });

                // Close mobile menu when clicking on links
                const mobileMenuLinks = mobileMenu.querySelectorAll('a');

                mobileMenuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenu.style.right = '-100%';
                        menuToggle.innerHTML = '<i class="fas fa-bars text-2xl"></i>';
                    });
                });

                // Hero Slider Animation
                const slides = document.querySelectorAll('.hero-slide');
                let currentSlide = 0;

                function showSlide(index) {
                    slides.forEach(slide => slide.classList.remove('active'));
                    slides[index].classList.add('active');
                }

                function nextSlide() {
                    currentSlide = (currentSlide + 1) % slides.length;
                    showSlide(currentSlide);
                }

                // Change slide every 5 seconds
                setInterval(nextSlide, 5000);

                // Smooth scrolling for navigation links
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();

                        const targetId = this.getAttribute('href');
                        if (targetId === '#') return;

                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            const navbarHeight = navbar.offsetHeight;
                            const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;

                            window.scrollTo({
                                top: targetPosition,
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            });
        </script> --}}

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Mobile menu toggle
                const menuToggle = document.getElementById('menu-toggle');
                const mobileMenu = document.getElementById('mobile-menu');

                if (menuToggle && mobileMenu) {
                    menuToggle.addEventListener('click', function() {
                        if (mobileMenu.style.right === '0px') {
                            mobileMenu.style.right = '-100%';
                            // Change icon
                            this.innerHTML = '<i class="fas fa-bars text-2xl"></i>';
                        } else {
                            mobileMenu.style.right = '0px';
                            // Change icon
                            this.innerHTML = '<i class="fas fa-times text-2xl"></i>';
                        }
                    });

                    // Ensure all mobile menu links have white text
                    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
                    mobileMenuLinks.forEach(link => {
                        link.style.color = 'white';

                        link.addEventListener('click', function() {
                            mobileMenu.style.right = '-100%';
                            menuToggle.innerHTML = '<i class="fas fa-bars text-2xl"></i>';
                        });
                    });
                }
            });
            </script>
    </body>
</html>
