<?php
/**
 * Dropdown Component
 * 
 * A dropdown menu component with trigger button and menu items
 * 
 * @param array $options Configuration options
 *   - string $id: Unique identifier for the dropdown
 *   - string $label: Button label text
 *   - string $variant: Button variant (primary, secondary, outline, ghost)
 *   - string $size: Size (xs, sm, md, lg, xl)
 *   - array $items: Array of menu items
 *     - string $label: Item label
 *     - string $value: Item value
 *     - string $icon: Optional icon
 *     - bool $divider: Show divider after item
 *     - bool $disabled: Disable item
 *   - string $align: Menu alignment (left, right)
 *   - string $class: Additional CSS classes
 * 
 * @return string HTML markup
 */
function Dropdown($options = []) {
    $defaults = [
        'id' => 'dropdown-' . uniqid(),
        'label' => 'Dropdown',
        'variant' => 'primary',
        'size' => 'md',
        'items' => [],
        'align' => 'left',
        'class' => ''
    ];
    
    $config = array_merge($defaults, $options);
    $dropdownId = htmlspecialchars($config['id']);
    $label = htmlspecialchars($config['label']);
    $variant = htmlspecialchars($config['variant']);
    $size = htmlspecialchars($config['size']);
    $align = htmlspecialchars($config['align']);
    $class = htmlspecialchars($config['class']);
    
    ob_start();
    ?>
    <div class="dropdown <?php echo $class; ?>" data-dropdown="<?php echo $dropdownId; ?>">
        <button 
            type="button"
            class="btn btn-<?php echo $variant; ?> btn-<?php echo $size; ?> dropdown-trigger"
            aria-haspopup="true"
            aria-expanded="false"
            data-dropdown-trigger="<?php echo $dropdownId; ?>"
        >
            <?php echo $label; ?>
            <svg class="dropdown-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
        
        <div 
            class="dropdown-menu dropdown-menu-<?php echo $align; ?>"
            data-dropdown-menu="<?php echo $dropdownId; ?>"
            role="menu"
        >
            <?php foreach ($config['items'] as $item): ?>
                <?php if (isset($item['divider']) && $item['divider']): ?>
                    <div class="dropdown-divider"></div>
                <?php else: ?>
                    <button
                        type="button"
                        class="dropdown-item <?php echo isset($item['disabled']) && $item['disabled'] ? 'disabled' : ''; ?>"
                        role="menuitem"
                        data-value="<?php echo htmlspecialchars($item['value'] ?? ''); ?>"
                        <?php echo isset($item['disabled']) && $item['disabled'] ? 'disabled' : ''; ?>
                    >
                        <?php if (isset($item['icon'])): ?>
                            <span class="dropdown-item-icon"><?php echo $item['icon']; ?></span>
                        <?php endif; ?>
                        <span class="dropdown-item-label"><?php echo htmlspecialchars($item['label']); ?></span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
    (function() {
        const dropdown = document.querySelector('[data-dropdown="<?php echo $dropdownId; ?>"]');
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = menu.classList.contains('show');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                m.classList.remove('show');
            });
            
            if (!isOpen) {
                menu.classList.add('show');
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                menu.classList.remove('show');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
        
        // Close on outside click
        document.addEventListener('click', function() {
            menu.classList.remove('show');
            trigger.setAttribute('aria-expanded', 'false');
        });
        
        // Handle item clicks
        menu.querySelectorAll('.dropdown-item:not(.disabled)').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.dataset.value;
                
                // Dispatch custom event
                dropdown.dispatchEvent(new CustomEvent('dropdown-select', {
                    detail: { value: value, label: this.textContent.trim() }
                }));
                
                menu.classList.remove('show');
                trigger.setAttribute('aria-expanded', 'false');
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
