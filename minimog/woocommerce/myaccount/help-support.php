<?php
/**
 * Help & Support - My Account Page
 *
 * @package Minimog
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
?>

<div class="modfolio-help-support">

    <div class="help-support-content-grid">

        <!-- Left Column - Support Options -->
        <div class="help-support-left-column">

            <!-- FAQ's Card -->
            <a href="#faqs-section" class="support-option-card">
                <div class="option-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="option-content">
                    <h3>FAQ's</h3>
                    <p>Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample</p>
                </div>
                <div class="option-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>

            <!-- Support Request Card -->
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'support-tickets' ) ); ?>" class="support-option-card">
                <div class="option-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="option-content">
                    <h3>Support Request</h3>
                    <p>Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample</p>
                </div>
                <div class="option-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>

            <!-- Submit a Ticket Card -->
            <a href="#submit-ticket" class="support-option-card" id="open-ticket-modal">
                <div class="option-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div class="option-content">
                    <h3>Submit a Ticket</h3>
                    <p>Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample</p>
                </div>
                <div class="option-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>

            <!-- Live Chat Card -->
            <a href="#live-chat" class="support-option-card" id="open-live-chat">
                <div class="option-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                </div>
                <div class="option-content">
                    <h3>Live Chat</h3>
                    <p>Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample Sample Text Here Sample</p>
                </div>
                <div class="option-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>

        </div>

        <!-- Right Column - Getting Started & Quick Links -->
        <div class="help-support-right-column">

            <!-- Getting Started Section -->
            <div class="getting-started-section" id="faqs-section">
                <div class="getting-started-header">
                    <h3>Getting Started</h3>
                    <button class="expand-collapse-btn" aria-expanded="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </button>
                </div>

                <div class="getting-started-content">
                    <!-- FAQ Item 1 -->
                    <div class="faq-item active">
                        <button class="faq-question" aria-expanded="true">
                            <span>How do I create my Modfolios account?</span>
                            <svg class="faq-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Click Join or Register, choose I'm a Creator / Talent, complete your profile, and upload your first portfolio to start earning.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <span>What type of content can I upload?</span>
                            <svg class="faq-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>You can upload professional photos, videos, or any creative visuals that you own or have full licensing rights to. All content must follow Modfolios' Content Policy.</p>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="faq-item">
                        <button class="faq-question" aria-expanded="false">
                            <span>Can I use my existing portfolio?</span>
                            <svg class="faq-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="faq-answer">
                            <p>Yes. You can re-upload or import your existing work as long as you own the copyright and licensing rights.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="quick-links-section">
                <a href="#" class="quick-link-item">
                    <span>Portfolio Management</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="#" class="quick-link-item">
                    <span>Licensing & Pricing</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="#" class="quick-link-item">
                    <span>Earnings and Payments</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="#" class="quick-link-item">
                    <span>Custom Shoots</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="#" class="quick-link-item">
                    <span>Subscriptions</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="#" class="quick-link-item">
                    <span>Account & Verification</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="#" class="quick-link-item">
                    <span>Support & Assistance</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ Accordion functionality
    const faqQuestions = document.querySelectorAll('.faq-question');

    faqQuestions.forEach(function(question) {
        question.addEventListener('click', function() {
            const faqItem = this.parentElement;
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            // Close all other FAQ items
            document.querySelectorAll('.faq-item').forEach(function(item) {
                item.classList.remove('active');
                item.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
            });

            // Toggle current item
            if (!isExpanded) {
                faqItem.classList.add('active');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Getting Started collapse/expand
    const expandBtn = document.querySelector('.expand-collapse-btn');
    if (expandBtn) {
        expandBtn.addEventListener('click', function() {
            const content = document.querySelector('.getting-started-content');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                content.style.display = 'none';
                this.setAttribute('aria-expanded', 'false');
                this.querySelector('svg').style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
                this.querySelector('svg').style.transform = 'rotate(0deg)';
            }
        });
    }
});
</script>
