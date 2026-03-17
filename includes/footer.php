    </main>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <span class="logo-up">Up</span><span class="logo-devix">Devix</span> <span class="logo-quiz">Quiz</span>
                    <p class="footer-text">Empowering learners with interactive quizzes and assessments.</p>
                </div>
                <div class="footer-links">
                    <a href="https://www.updevix.com" target="_blank">UpDevix Website</a>
                    <a href="https://www.updevix.com/contact" target="_blank">Contact Us</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved. | Powered by <a href="https://www.updevix.com" target="_blank">UpDevix</a></p>
            </div>
        </div>
    </footer>
    <script src="/assets/js/app.js"></script>
    <?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>
