        </main>
    </div>
    
    <style>
    /* Mobile table scrolling improvements */
    @media (max-width: 768px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
        .overflow-x-auto table {
            min-width: 600px;
        }
        /* Better touch targets on mobile */
        button, a.btn, input[type="submit"] {
            min-height: 44px;
            min-width: 44px;
        }
        /* Improve text readability on small screens */
        body {
            font-size: 14px;
        }
        /* Better spacing for mobile cards */
        .bg-white.rounded-xl {
            margin-left: -1rem;
            margin-right: -1rem;
            border-radius: 0;
        }
        @media (min-width: 640px) {
            .bg-white.rounded-xl {
                margin-left: 0;
                margin-right: 0;
                border-radius: 0.75rem;
            }
        }
    }
    </style>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

