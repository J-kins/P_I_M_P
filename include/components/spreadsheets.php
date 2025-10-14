
<?php
/**
 * Spreadsheet components for PHP UI Template System
 */

/**
 * Basic spreadsheet component
 * 
 * @param array $params Parameters for the spreadsheet
 * @return string The HTML for the spreadsheet
 */
function spreadsheet($params = []) {
    // Default parameters
    $defaults = [
        'id' => 'spreadsheet-' . uniqid(),
        'rows' => 20,
        'columns' => 10,
        'class' => '',
        'editable' => true,
        'showToolbar' => true
    ];
    
    // Merge params with defaults
    $params = array_merge($defaults, $params);
    
    // Generate column headers (A-Z, AA-ZZ)
    $column_headers = [];
    for ($i = 0; $i < $params['columns']; $i++) {
        $column_headers[] = get_column_letter($i);
    }
    
    ob_start();
    ?>
    <div 
        class="spreadsheet-container <?= htmlspecialchars($params['class']) ?>" 
        id="<?= htmlspecialchars($params['id']) ?>"
        data-ng-app="spreadsheetApp"
        data-ng-controller="SpreadsheetController"
    >
        <?php if ($params['showToolbar']): ?>
        <div class="spreadsheet-toolbar">
            <div class="toolbar-group">
                <button class="toolbar-button" title="Bold"><i class="icon-bold"></i></button>
                <button class="toolbar-button" title="Italic"><i class="icon-italic"></i></button>
                <button class="toolbar-button" title="Underline"><i class="icon-underline"></i></button>
            </div>
            <div class="toolbar-group">
                <button class="toolbar-button" title="Align Left"><i class="icon-align-left"></i></button>
                <button class="toolbar-button" title="Align Center"><i class="icon-align-center"></i></button>
                <button class="toolbar-button" title="Align Right"><i class="icon-align-right"></i></button>
            </div>
            <div class="toolbar-group">
                <button class="toolbar-button" title="Add Row"><i class="icon-plus"></i> Row</button>
                <button class="toolbar-button" title="Add Column"><i class="icon-plus"></i> Column</button>
            </div>
        </div>
        <?php endif; ?>

        <div class="spreadsheet" data-ng-cloak>
            <div class="cell-address-display">{{ activeCell }}</div>
            
            <div class="spreadsheet-table-container">
                <table class="spreadsheet-table">
                    <thead>
                        <tr>
                            <th class="row-header-cell"></th>
                            <?php foreach ($column_headers as $header): ?>
                            <th class="column-header-cell"><?= htmlspecialchars($header) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($row = 1; $row <= $params['rows']; $row++): ?>
                        <tr>
                            <th class="row-header-cell"><?= $row ?></th>
                            <?php foreach ($column_headers as $colIndex => $colLetter): ?>
                            <td 
                                class="spreadsheet-cell" 
                                data-cell="<?= $colLetter . $row ?>" 
                                data-ng-class="{'active': activeCell == '<?= $colLetter . $row ?>'}"
                                data-ng-click="selectCell('<?= $colLetter . $row ?>')"
                                data-ng-model="cells['<?= $colLetter . $row ?>'].value"
                                contenteditable="<?= $params['editable'] ? 'true' : 'false' ?>"
                                data-ng-blur="updateCell('<?= $colLetter . $row ?>', $event)"
                            >{{ getCellValue('<?= $colLetter . $row ?>') }}</td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Advanced spreadsheet with multiple sheets and more features
 * 
 * @param array $params Parameters for the spreadsheet
 * @return string The HTML for the spreadsheet
 */
function advancedSpreadsheet($params = []) {
    // Default parameters
    $defaults = [
        'id' => 'advanced-spreadsheet-' . uniqid(),
        'sheets' => [
            ['name' => 'Sheet 1', 'rows' => 20, 'columns' => 10],
            ['name' => 'Sheet 2', 'rows' => 20, 'columns' => 10],
        ],
        'class' => '',
        'editable' => true,
        'showToolbar' => true,
        'showFormulaBar' => true
    ];
    
    // Merge params with defaults
    $params = array_merge($defaults, $params);
    
    ob_start();
    ?>
    <div 
        class="advanced-spreadsheet-container <?= htmlspecialchars($params['class']) ?>" 
        id="<?= htmlspecialchars($params['id']) ?>"
        data-ng-app="spreadsheetApp"
        data-ng-controller="SpreadsheetController"
    >
        <?php if ($params['showToolbar']): ?>
        <div class="spreadsheet-toolbar">
            <div class="toolbar-group">
                <button class="toolbar-button" title="Bold"><i class="icon-bold"></i></button>
                <button class="toolbar-button" title="Italic"><i class="icon-italic"></i></button>
                <button class="toolbar-button" title="Underline"><i class="icon-underline"></i></button>
            </div>
            <div class="toolbar-group">
                <button class="toolbar-button" title="Align Left"><i class="icon-align-left"></i></button>
                <button class="toolbar-button" title="Align Center"><i class="icon-align-center"></i></button>
                <button class="toolbar-button" title="Align Right"><i class="icon-align-right"></i></button>
            </div>
            <div class="toolbar-group">
                <button class="toolbar-button" title="Add Row"><i class="icon-plus"></i> Row</button>
                <button class="toolbar-button" title="Add Column"><i class="icon-plus"></i> Column</button>
                <button class="toolbar-button" title="Add Sheet"><i class="icon-plus"></i> Sheet</button>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($params['showFormulaBar']): ?>
        <div class="formula-bar">
            <div class="cell-address">{{ activeCell }}</div>
            <div class="formula-input-container">
                <span class="formula-prefix">=</span>
                <input type="text" class="formula-input" data-ng-model="cells[activeCell].formula" data-ng-change="updateFormula(activeCell)">
            </div>
        </div>
        <?php endif; ?>

        <div class="spreadsheet-tabs">
            <div 
                class="spreadsheet-tab" 
                data-ng-repeat="sheet in sheets"
                data-ng-class="{'active': currentSheetIndex == $index}"
                data-ng-click="selectSheet($index)"
            >
                {{ sheet.name }}
            </div>
            <div class="spreadsheet-tab add-sheet">
                <i class="icon-plus"></i>
            </div>
        </div>

        <div class="spreadsheet-content" data-ng-repeat="sheet in sheets" data-ng-show="currentSheetIndex == $index">
            <div class="spreadsheet-table-container">
                <table class="spreadsheet-table">
                    <thead>
                        <tr>
                            <th class="row-header-cell"></th>
                            <th class="column-header-cell" data-ng-repeat="col in sheet.columnHeaders">{{ col }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-ng-repeat="row in sheet.rowRange">
                            <th class="row-header-cell">{{ row }}</th>
                            <td 
                                class="spreadsheet-cell" 
                                data-ng-repeat="col in sheet.columnHeaders" 
                                data-cell="{{ col + row }}" 
                                data-ng-class="{'active': activeCell == col + row && currentSheetIndex == $parent.$parent.$index}"
                                data-ng-click="selectCell(col + row, $parent.$parent.$index)"
                                data-ng-model="cells[currentSheetIndex + '_' + col + row].value"
                                contenteditable="<?= $params['editable'] ? 'true' : 'false' ?>"
                                data-ng-blur="updateCell(col + row, $event, $parent.$parent.$index)"
                            >{{ getCellValue(col + row, $parent.$parent.$index) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Helper to get column letter from index
 * 
 * @param int $index Column index (0-based)
 * @return string Column letter (A, B, C, ... Z, AA, AB, ...)
 */
function get_column_letter($index) {
    $dividend = $index + 1;
    $columnName = '';
    
    while ($dividend > 0) {
        $modulo = ($dividend - 1) % 26;
        $columnName = chr(65 + $modulo) . $columnName;
        $dividend = (int)(($dividend - $modulo) / 26);
    }
    
    return $columnName;
}
?>
