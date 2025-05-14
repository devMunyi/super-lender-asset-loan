<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Summary Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
            height: auto;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Logo -->
    <div class="logo">
        <img src="https://via.placeholder.com/150x50?text=Your+Logo" alt="Company Logo">
    </div>

    <!-- Greeting -->
    <p>Hi User,</p>

    <!-- Date -->
    <p>Date: <strong>October 25, 2023</strong></p>

    <!-- Title -->
    <div class="title">Performance Summary</div>

    <!-- Disbursements Table -->
    <h3>Disbursements</h3>
    <table>
        <thead>
        <tr>
            <th>Branch</th>
            <th>Target</th>
            <th>Actual</th>
            <th>Deficit</th>
            <th>Deficit %</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Branch A</td>
            <td>$100,000</td>
            <td>$90,000</td>
            <td>$10,000</td>
            <td>10%</td>
        </tr>
        <tr>
            <td>Branch B</td>
            <td>$150,000</td>
            <td>$140,000</td>
            <td>$10,000</td>
            <td>6.67%</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>$250,000</strong></td>
            <td><strong>$230,000</strong></td>
            <td><strong>$20,000</strong></td>
            <td><strong>8%</strong></td>
        </tr>
        </tbody>
    </table>

    <!-- Collections Table -->
    <h3>Collections</h3>
    <table>
        <thead>
        <tr>
            <th>Due</th>
            <th>Collected</th>
            <th>Balance</th>
            <th>Collection Rate</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>$200,000</td>
            <td>$180,000</td>
            <td>$20,000</td>
            <td>90%</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>$180,000</strong></td>
            <td><strong>$20,000</strong></td>
            <td><strong>90%</strong></td>
        </tr>
        </tbody>
    </table>

    <!-- Customer Acquisition Table -->
    <h3>Customer Acquisition</h3>
    <table>
        <thead>
        <tr>
            <th>New</th>
            <th>Repeat</th>
            <th>Leads</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>50</td>
            <td>30</td>
            <td>100</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>30</strong></td>
            <td><strong>100</strong></td>
        </tr>
        </tbody>
    </table>

    <!-- Interactions Table -->
    <h3>Interactions</h3>
    <table>
        <thead>
        <tr>
            <th>Total Interactions</th>
            <th>PTPs</th>
            <th>PTPs Amounts</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>500</td>
            <td>200</td>
            <td>$50,000</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong>200</strong></td>
            <td><strong>$50,000</strong></td>
        </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>This is an automated email. Please do not reply.</p>
        <p>&copy; 2023 Your Company. All rights reserved.</p>
    </div>
</div>
</body>
</html>