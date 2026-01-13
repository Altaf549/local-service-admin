<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - Local Service Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .prose {
            font-size: 16px;
            line-height: 1.6;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .prose h1, .prose h2, .prose h3 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .prose h1 { font-size: 1.5rem; }
        .prose h2 { font-size: 1.25rem; }
        .prose h3 { font-size: 1.125rem; }
        .prose p {
            margin-bottom: 1rem;
        }
        .prose ul, .prose ol {
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .prose li {
            margin-bottom: 0.5rem;
        }
        .prose a {
            color: #3b82f6;
            text-decoration: underline;
        }
        .prose strong, .prose b {
            font-weight: 600;
        }
        .prose em, .prose i {
            font-style: italic;
        }
        .prose blockquote {
            border-left: 4px solid #e5e7eb;
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #6b7280;
        }
        
        @media (max-width: 640px) {
            .prose {
                font-size: 14px;
                line-height: 1.5;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .prose h1 { font-size: 1.25rem; }
            .prose h2 { font-size: 1.125rem; }
            .prose h3 { font-size: 1rem; }
            .prose ul, .prose ol {
                padding-left: 1rem;
            }
            .prose * {
                max-width: 100% !important;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-6 px-3 sm:py-8 sm:px-6 lg:py-12 lg:px-8">
        <div class="max-w-none sm:max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-6 sm:mb-8 lg:mb-12">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-2 sm:mb-4">Terms and Conditions</h1>
                <p class="text-sm sm:text-base lg:text-lg text-gray-600">Please read these terms carefully</p>
            </div>

            <!-- Content -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 lg:p-8">
                    @if($terms && $terms->value)
                        <div class="prose max-w-none">
                            {!! $terms->value !!}
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400 mb-3 sm:mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Terms and Conditions Not Available</h3>
                            <p class="text-sm sm:text-base text-gray-500">The terms and conditions content is currently being updated. Please check back later.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 sm:mt-8 text-center">
                <a href="javascript:window.close()" class="inline-flex items-center px-4 py-2 sm:px-6 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Close Window
                </a>
            </div>
        </div>
    </div>
</body>
</html>
