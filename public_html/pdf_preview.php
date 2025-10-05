<?php
include("session.php");

// Set default time period
$period = isset($_GET['period']) ? $_GET['period'] : 'monthly';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

// Generate report data (same function as before)
function generateReport($con, $userid, $period, $year, $month) {
    $data = array();
    
    switch($period) {
        case 'yearly':
            $query = "SELECT YEAR(incomedate) as year, 
                      SUM(income) as total_income,
                      (SELECT SUM(expense) FROM expenses WHERE user_id='$userid' AND YEAR(expensedate) = YEAR(i.incomedate)) as total_expense
                      FROM income i 
                      WHERE user_id='$userid' 
                      GROUP BY YEAR(incomedate) 
                      ORDER BY year DESC";
            break;
            
        case 'monthly':
            $query = "SELECT MONTH(incomedate) as month, 
                      SUM(income) as total_income,
                      (SELECT SUM(expense) FROM expenses WHERE user_id='$userid' AND YEAR(expensedate) = '$year' AND MONTH(expensedate) = MONTH(i.incomedate)) as total_expense
                      FROM income i 
                      WHERE user_id='$userid' AND YEAR(incomedate) = '$year'
                      GROUP BY MONTH(incomedate) 
                      ORDER BY month DESC";
            break;
            
        case 'weekly':
            $query = "SELECT WEEK(incomedate, 1) as week, 
                      SUM(income) as total_income,
                      (SELECT SUM(expense) FROM expenses WHERE user_id='$userid' AND YEAR(expensedate) = '$year' AND MONTH(expensedate) = '$month' AND WEEK(expensedate, 1) = WEEK(i.incomedate, 1)) as total_expense
                      FROM income i 
                      WHERE user_id='$userid' AND YEAR(incomedate) = '$year' AND MONTH(incomedate) = '$month'
                      GROUP BY WEEK(incomedate, 1) 
                      ORDER BY week DESC";
            break;
    }
    
    $result = mysqli_query($con, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

$reportData = generateReport($con, $userid, $period, $year, $month);

// Handle PDF download
if(isset($_GET['download']) && $_GET['download'] == 'true') {
    require('../core/fpdf186/fpdf.php');
    
    // Create new PDF document
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('Arial','B',16);
    
    // Title
    $pdf->Cell(0,10,'Financial Report - '.ucfirst($period).' View',0,1,'C');
    $pdf->SetFont('Arial','',12);
    
    // Period info
    $periodInfo = "Year: $year";
    if($period != 'yearly') $periodInfo .= ", Month: ".date('F', mktime(0, 0, 0, $month, 10));
    $pdf->Cell(0,10,$periodInfo,0,1);
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(40,10,'Period',1,0,'C');
    $pdf->Cell(40,10,'Income',1,0,'C');
    $pdf->Cell(40,10,'Expense',1,0,'C');
    $pdf->Cell(40,10,'Balance',1,1,'C');
    $pdf->SetFont('Arial','',12);
    
    // Table data
    $totalIncome = 0;
    $totalExpense = 0;
    
    foreach($reportData as $row) {
        $periodLabel = '';
        switch($period) {
            case 'yearly': $periodLabel = $row['year']; break;
            case 'monthly': $periodLabel = date('F', mktime(0, 0, 0, $row['month'], 10)); break;
            case 'weekly': $periodLabel = 'Week '.$row['week']; break;
        }
        
        $income = $row['total_income'] ? $row['total_income'] : 0;
        $expense = $row['total_expense'] ? $row['total_expense'] : 0;
        $balance = $income - $expense;
        
        $totalIncome += $income;
        $totalExpense += $expense;
        
        $pdf->Cell(40,10,$periodLabel,1,0,'L');
        $pdf->Cell(40,10,number_format($income, 2),1,0,'R');
        $pdf->Cell(40,10,number_format($expense, 2),1,0,'R');
        $pdf->Cell(40,10,number_format($balance, 2),1,1,'R');
    }
    
    // Footer with totals
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(40,10,'Total',1,0,'L');
    $pdf->Cell(40,10,number_format($totalIncome, 2),1,0,'R');
    $pdf->Cell(40,10,number_format($totalExpense, 2),1,0,'R');
    $pdf->Cell(40,10,number_format($totalIncome - $totalExpense, 2),1,1,'R');
    
    // Output PDF
    $pdf->Output('D','financial_report_'.$period.'_'.date('Ymd').'.pdf');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PDF Preview - Financial Report</title>
    <link href="core/css/bootstrap.min.css" rel="stylesheet">
    <script src="core/js/feather.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .pdf-preview {
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .report-period {
            font-size: 16px;
            color: #666;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .report-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            padding: 8px 20px;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="pdf-preview">
        <div class="report-header">
            <div class="report-title">Financial Report - <?php echo ucfirst($period); ?> View</div>
            <div class="report-period">
                Year: <?php echo $year; ?>
                <?php if($period != 'yearly'): ?>
                , Month: <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th class="text-right">Income</th>
                    <th class="text-right">Expense</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalIncome = 0;
                $totalExpense = 0;
                
                foreach($reportData as $row): 
                    $periodLabel = '';
                    switch($period) {
                        case 'yearly': $periodLabel = $row['year']; break;
                        case 'monthly': $periodLabel = date('F', mktime(0, 0, 0, $row['month'], 10)); break;
                        case 'weekly': $periodLabel = 'Week '.$row['week']; break;
                    }
                    
                    $income = $row['total_income'] ? $row['total_income'] : 0;
                    $expense = $row['total_expense'] ? $row['total_expense'] : 0;
                    $balance = $income - $expense;
                    
                    $totalIncome += $income;
                    $totalExpense += $expense;
                ?>
                <tr>
                    <td><?php echo $periodLabel; ?></td>
                    <td class="text-right text-success"><?php echo number_format($income, 2); ?></td>
                    <td class="text-right text-danger"><?php echo number_format($expense, 2); ?></td>
                    <td class="text-right <?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo number_format($balance, 2); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="text-right text-success"><strong><?php echo number_format($totalIncome, 2); ?></strong></td>
                    <td class="text-right text-danger"><strong><?php echo number_format($totalExpense, 2); ?></strong></td>
                    <td class="text-right <?php echo ($totalIncome - $totalExpense) >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <strong><?php echo number_format($totalIncome - $totalExpense, 2); ?></strong>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <div class="action-buttons">
            <a href="report.php" class="btn btn-secondary">Back to Report</a>
            <a href="pdf_preview.php?download=true&period=<?php echo $period; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn btn-primary">Download PDF</a>
        </div>
    </div>
    
    <script>
        feather.replace();
    </script>
</body>
</html>