<?php
/**
 * Modal Component
 * 
 * A modal dialog component with overlay, header, body, and footer
 * 
 * @param array $options Configuration options
 *   - string $id: Unique identifier for the modal
 *   - string $title: Modal title
 *   - string $content: Modal body content (HTML)
 *   - string $size: Size (sm, md, lg, xl, full)
 *   - bool $showClose: Show close button in header
 *   - array $footer: Footer buttons configuration
 *   - string $class: Additional CSS classes
 * 
 * @return string HTML markup
 */
function Modal($options = []) {
    $defaults = [
        'id' => 'modal-' . uniqid(),
        'title' => 'Modal Title',
        'content' => '',
        'size' => 'md',
        'showClose' => true,
        'footer' => [],
        'class' => ''
    ];
    
    $config = array_merge($defaults, $options);
    $modalId = htmlspecialchars($config['id']);
    $title = htmlspecialchars($config['title']);
    $size = htmlspecialchars($config['size']);
    $class = htmlspecialchars($config['class']);
    
    ob_start();
    ?>
    <div class="modal-overlay <?php echo $class; ?>" id="<?php echo $modalId; ?>" role="dialog" aria-modal="true" aria-labelledby="<?php echo $modalId; ?>-title">
        <div class="modal modal-<?php echo $size; ?>">
            <div class="modal-header">
                <h2 class="modal-title" id="<?php echo $modalId; ?>-title"><?php echo $title; ?></h2>
                <?php if ($config['showClose']): ?>
                    <button type="button" class="modal-close" aria-label="Close" data-modal-close="<?php echo $modalId; ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="modal-body">
                <?php echo $config['content']; ?>
            </div>
            
            <?php if (!empty($config['footer'])): ?>
                <div class="modal-footer">
                    <?php foreach ($config['footer'] as $button): ?>
                        <?php
                        $btnVariant = $button['variant'] ?? 'primary';
                        $btnSize = $button['size'] ?? 'md';
                        $btnLabel = htmlspecialchars($button['label'] ?? 'Button');
                        $btnAction = $button['action'] ?? '';
                        ?>
                        <button 
                            type="button" 
                            class="btn btn-<?php echo $btnVariant; ?> btn-<?php echo $btnSize; ?>"
                            <?php if ($btnAction === 'close'): ?>
                                data-modal-close="<?php echo $modalId; ?>"
                            <?php endif; ?>
                        >
                            <?php echo $btnLabel; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    (function() {
        const modal = document.getElementById('<?php echo $modalId; ?>');
        const closeButtons = modal.querySelectorAll('[data-modal-close]');
        
        // Close on button click
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            });
        });
        
        // Close on overlay click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
        
        // Global function to open modal
        window.openModal = function(id) {
            const targetModal = document.getElementById(id);
            if (targetModal) {
                targetModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        };
    })();
    </script>
    <?php
    return ob_get_clean();
}
