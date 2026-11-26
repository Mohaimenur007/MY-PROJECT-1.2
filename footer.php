</main>

<footer class="site-footer">
    <div class="container-xl">
        <div class="row gy-4">
            <div class="col-lg-4">
                <span class="logo small"><span class="logo-mark"><?= ic("pin") ?></span>Lostly</span>
                <p class="mt-3 mb-0 footer-about">The lost &amp; found network for Bangladesh. Free to use, run by the community.</p>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <h2 class="footer-title">Platform</h2>
                <ul class="footer-links">
                    <li><a href="browse.php">Browse reports</a></li>
                    <li><a href="report_item.php">Report an item</a></li>
                    <li><a href="reunited.php">Reunited</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <h2 class="footer-title">Help</h2>
                <ul class="footer-links">
                    <li><a href="page.php?p=faq">Questions &amp; answers</a></li>
                    <li><a href="page.php?p=safety">Safety tips</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <h2 class="footer-title">Lostly</h2>
                <ul class="footer-links">
                    <li><a href="page.php?p=about">About</a></li>
                    <li><a href="page.php?p=terms">Terms of use</a></li>
                    <li><a href="page.php?p=privacy">Privacy</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-12 col-lg-2">
                <h2 class="footer-title">Emergency</h2>
                <ul class="footer-links">
                    <li>National helpline <b>999</b></li>
                    <li>Lost documents? File a GD at your nearest police station.</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date("Y") ?> Lostly · Group 6</span>
            <span class="footer-note">Made in Bangladesh</span>
        </div>
    </div>
</footer>

<!-- One shared "are you sure?" dialog for every form marked with data-confirm -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="confirmModalTitle">Are you sure?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body js-confirm-text"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-lost js-confirm-yes">Yes, continue</button>
            </div>
        </div>
    </div>
</div>

<script src="js/vendor/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
