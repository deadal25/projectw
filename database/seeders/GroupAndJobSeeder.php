<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Job;
use Illuminate\Database\Seeder;

class GroupAndJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupsData = [
            1 => [
                'name' => 'Capacity & Prod Plan Control',
                'jobs' => [
                    ['code' => 'PPIC-01', 'name' => 'Senior Staff Capacity Plan'],
                    ['code' => 'PPIC-02', 'name' => 'Staff Capacity Planning & Master Schedule'],
                    ['code' => 'PPIC-03', 'name' => 'Foreman Production Control Line'],
                    ['code' => 'PPIC-04', 'name' => 'Leader Production Control'],
                    ['code' => 'PPIC-05', 'name' => 'Staff Production Control & Daily Dispatch'],
                    ['code' => 'PPIC-06', 'name' => 'Foreman Delivery Control'],
                    ['code' => 'PPIC-07', 'name' => 'Leader Delivery Control & Loading'],
                    ['code' => 'PPIC-08', 'name' => 'Staff Delivery Control & Order Tracking'],
                    ['code' => 'PPIC-09', 'name' => 'Operator Delivery Control & Staging'],
                    ['code' => 'PPIC-10', 'name' => 'Admin PPIC & Production Reporting'],
                ],
            ],
            2 => [
                'name' => 'Inventory Control & New Model',
                'jobs' => [
                    ['code' => 'ICNM-01', 'name' => 'Senior Staff Inventory Control'],
                    ['code' => 'ICNM-02', 'name' => 'Staff Raw Material & Parts Inventory'],
                    ['code' => 'ICNM-03', 'name' => 'Foreman Material Warehouse'],
                    ['code' => 'ICNM-04', 'name' => 'Leader Finished Goods Warehouse'],
                    ['code' => 'ICNM-05', 'name' => 'Staff New Model Development Coordination'],
                    ['code' => 'ICNM-06', 'name' => 'Leader Trial Production & Pre-Series'],
                    ['code' => 'ICNM-07', 'name' => 'Operator Material Handling & Forklift'],
                    ['code' => 'ICNM-08', 'name' => 'Staff WIP & Stock Opname Control'],
                    ['code' => 'ICNM-09', 'name' => 'Inspector Parts Inflow & Verification'],
                    ['code' => 'ICNM-10', 'name' => 'Admin Inventory & New Model'],
                ],
            ],
            3 => [
                'name' => 'Die Engineering',
                'jobs' => [
                    ['code' => 'DIE-01', 'name' => 'Senior Staff Die Design & Simulation'],
                    ['code' => 'DIE-02', 'name' => 'Staff CAD/CAM Die Modeling'],
                    ['code' => 'DIE-03', 'name' => 'Foreman Die Maintenance & Overhaul'],
                    ['code' => 'DIE-04', 'name' => 'Leader Die Repair & Modification'],
                    ['code' => 'DIE-05', 'name' => 'Technician Wire Cut & EDM Machine'],
                    ['code' => 'DIE-06', 'name' => 'Technician CNC Machining Center Dies'],
                    ['code' => 'DIE-07', 'name' => 'Staff Try Out & Die Sample Approval'],
                    ['code' => 'DIE-08', 'name' => 'Inspector Die Quality & Dimension'],
                    ['code' => 'DIE-09', 'name' => 'Maintenance Press Tooling & Spare Part'],
                    ['code' => 'DIE-10', 'name' => 'Admin Die Engineering & Tool Room'],
                ],
            ],
            4 => [
                'name' => 'M/ME 1',
                'jobs' => [
                    ['code' => 'ME1-01', 'name' => 'Senior Staff Machining Process Line 1'],
                    ['code' => 'ME1-02', 'name' => 'Staff Process Engineering Camshaft'],
                    ['code' => 'ME1-03', 'name' => 'Foreman CNC Turning & Milling Line 1'],
                    ['code' => 'ME1-04', 'name' => 'Leader Gear Hobbing & Broaching'],
                    ['code' => 'ME1-05', 'name' => 'Technician CNC Program & Setting Line 1'],
                    ['code' => 'ME1-06', 'name' => 'Leader Heat Treatment Line 1'],
                    ['code' => 'ME1-07', 'name' => 'Operator Line Auto Machining 1'],
                    ['code' => 'ME1-08', 'name' => 'Maintenance Tech SPM Line 1'],
                    ['code' => 'ME1-09', 'name' => 'Tooling & Fixture Specialist Line 1'],
                    ['code' => 'ME1-10', 'name' => 'Admin Machining & ME Line 1'],
                ],
            ],
            5 => [
                'name' => 'M/ME 2',
                'jobs' => [
                    ['code' => 'ME2-01', 'name' => 'Senior Staff Machining Process Line 2'],
                    ['code' => 'ME2-02', 'name' => 'Staff Process Engineering Bevel Gear'],
                    ['code' => 'ME2-03', 'name' => 'Foreman Grinding & Superfinishing Line 2'],
                    ['code' => 'ME2-04', 'name' => 'Leader Washing & Deburring Line 2'],
                    ['code' => 'ME2-05', 'name' => 'Technician CNC Cylindrical Grinding 2'],
                    ['code' => 'ME2-06', 'name' => 'Leader Quality Control In-Process Line 2'],
                    ['code' => 'ME2-07', 'name' => 'Operator Line Auto Machining 2'],
                    ['code' => 'ME2-08', 'name' => 'Maintenance Tech SPM Line 2'],
                    ['code' => 'ME2-09', 'name' => 'Tooling & Fixture Specialist Line 2'],
                    ['code' => 'ME2-10', 'name' => 'Admin Machining & ME Line 2'],
                ],
            ],
            6 => [
                'name' => 'Product Engineering',
                'jobs' => [
                    ['code' => 'PE-01', 'name' => 'Senior Staff Product Design & R&D'],
                    ['code' => 'PE-02', 'name' => 'Staff 3D CAD Drawing & SolidWorks'],
                    ['code' => 'PE-03', 'name' => 'Staff Engineering Change Order (ECO/ECN)'],
                    ['code' => 'PE-04', 'name' => 'Leader Prototype Testing & Validation'],
                    ['code' => 'PE-05', 'name' => 'Staff Product Cost Estimation & VA/VE'],
                    ['code' => 'PE-06', 'name' => 'Testing Engineer NVH & Fatigue Life'],
                    ['code' => 'PE-07', 'name' => 'Metallurgical & Materials Lab Specialist'],
                    ['code' => 'PE-08', 'name' => 'Technical Documentation & Drawing Control'],
                    ['code' => 'PE-09', 'name' => 'Homologation & Spec Analyst'],
                    ['code' => 'PE-10', 'name' => 'Admin Product Engineering'],
                ],
            ],
            7 => [
                'name' => 'QA Ckr',
                'jobs' => [
                    ['code' => 'QAC-01', 'name' => 'Senior Staff QA Plant Cikarang'],
                    ['code' => 'QAC-02', 'name' => 'Staff CMM & 3D Metrology Measurement'],
                    ['code' => 'QAC-03', 'name' => 'Foreman Receiving Inspection Cikarang'],
                    ['code' => 'QAC-04', 'name' => 'Leader In-Process QA Line Cikarang'],
                    ['code' => 'QAC-05', 'name' => 'Staff Outgoing QA & Final Audit Cikarang'],
                    ['code' => 'QAC-06', 'name' => 'Specialist Calibration & Gauges Ckr'],
                    ['code' => 'QAC-07', 'name' => 'Claim Customer & 8D Analysis Cikarang'],
                    ['code' => 'QAC-08', 'name' => 'Inspector Layout Inspection & Hardness'],
                    ['code' => 'QAC-09', 'name' => 'Leader Containment & Non-Conformance Ckr'],
                    ['code' => 'QAC-10', 'name' => 'Admin QA Cikarang'],
                ],
            ],
            8 => [
                'name' => 'QA Krw',
                'jobs' => [
                    ['code' => 'QAK-01', 'name' => 'Senior Staff QA Plant Karawang'],
                    ['code' => 'QAK-02', 'name' => 'Staff Quality Management System (IATF 16949)'],
                    ['code' => 'QAK-03', 'name' => 'Foreman Receiving Inspection Karawang'],
                    ['code' => 'QAK-04', 'name' => 'Leader Final Product Audit Karawang'],
                    ['code' => 'QAK-05', 'name' => 'Staff Reliability & Endurance Testing Krw'],
                    ['code' => 'QAK-06', 'name' => 'Chemical & Cleanliness Analysis Specialist'],
                    ['code' => 'QAK-07', 'name' => 'Claim Customer & 8D Analysis Karawang'],
                    ['code' => 'QAK-08', 'name' => 'Inspector Precision Gauge & Roundness Krw'],
                    ['code' => 'QAK-09', 'name' => 'Leader Sorting & Defect Prevention Krw'],
                    ['code' => 'QAK-10', 'name' => 'Admin QA Karawang'],
                ],
            ],
            9 => [
                'name' => 'Proc. & New Business',
                'jobs' => [
                    ['code' => 'PROC-01', 'name' => 'Senior Staff Supplier Development'],
                    ['code' => 'PROC-02', 'name' => 'Staff Local Parts & Raw Material Purchasing'],
                    ['code' => 'PROC-03', 'name' => 'Staff Import Parts & Customs Clearance'],
                    ['code' => 'PROC-04', 'name' => 'Staff Cost Reduction & Benchmarking'],
                    ['code' => 'PROC-05', 'name' => 'Leader Supplier Quality Assurance (SQA)'],
                    ['code' => 'PROC-06', 'name' => 'Staff Subcontractor & Treatment Sourcing'],
                    ['code' => 'PROC-07', 'name' => 'Market Intelligence & New Business Analyst'],
                    ['code' => 'PROC-08', 'name' => 'Commercial Contract & Price Negotiation'],
                    ['code' => 'PROC-09', 'name' => 'Expediting & Supplier Delivery Monitoring'],
                    ['code' => 'PROC-10', 'name' => 'Admin Procurement & Vendor Database'],
                ],
            ],
            10 => [
                'name' => 'HR & GA',
                'jobs' => [
                    ['code' => 'HRGA-01', 'name' => 'Senior Staff People Development & Training'],
                    ['code' => 'HRGA-02', 'name' => 'Staff Recruitment & Talent Acquisition'],
                    ['code' => 'HRGA-03', 'name' => 'Foreman Industrial Relations & Compliance'],
                    ['code' => 'HRGA-04', 'name' => 'Staff Payroll, Compensation & Benefits'],
                    ['code' => 'HRGA-05', 'name' => 'Leader Environmental, Health & Safety (EHS)'],
                    ['code' => 'HRGA-06', 'name' => 'Staff Building, Utility & Fleet Maintenance'],
                    ['code' => 'HRGA-07', 'name' => 'Leader Security & Factory Protocol'],
                    ['code' => 'HRGA-08', 'name' => 'Staff Occupational Health Clinic & Medical'],
                    ['code' => 'HRGA-09', 'name' => 'Community Relations, Legal & CSR Specialist'],
                    ['code' => 'HRGA-10', 'name' => 'Admin Human Resources & General Affairs'],
                ],
            ],
            11 => [
                'name' => 'Finance Acct & System',
                'jobs' => [
                    ['code' => 'FIN-01', 'name' => 'Senior Staff Cost Accounting & Budgeting'],
                    ['code' => 'FIN-02', 'name' => 'Staff Financial Accounting & Corporate Tax'],
                    ['code' => 'FIN-03', 'name' => 'Staff Treasury, Banking & Cashflow'],
                    ['code' => 'FIN-04', 'name' => 'Staff Accounts Payable & Receivable'],
                    ['code' => 'FIN-05', 'name' => 'Senior Staff IT Infrastructure & Network'],
                    ['code' => 'FIN-06', 'name' => 'Staff ERP & Enterprise Database Admin'],
                    ['code' => 'FIN-07', 'name' => 'Staff Internal Audit & Financial Control'],
                    ['code' => 'FIN-08', 'name' => 'IT Technical Support & Hardware Specialist'],
                    ['code' => 'FIN-09', 'name' => 'Fixed Asset & Depreciation Accountant'],
                    ['code' => 'FIN-10', 'name' => 'Admin Finance, Accounting & IT System'],
                ],
            ],
            12 => [
                'name' => 'Sales & Marketing (2W & 4W)',
                'jobs' => [
                    ['code' => 'SALES-01', 'name' => 'Senior Staff OEM Sales 2W (Two-Wheeler)'],
                    ['code' => 'SALES-02', 'name' => 'Senior Staff OEM Sales 4W (Four-Wheeler)'],
                    ['code' => 'SALES-03', 'name' => 'Staff Aftermarket & Spare Parts Sales'],
                    ['code' => 'SALES-04', 'name' => 'Staff Export Market & International Logistics'],
                    ['code' => 'SALES-05', 'name' => 'Staff Demand Forecasting & Customer Order'],
                    ['code' => 'SALES-06', 'name' => 'Commercial Pricing & Quotation Analyst'],
                    ['code' => 'SALES-07', 'name' => 'Logistics, Packaging & Shipping Specialist'],
                    ['code' => 'SALES-08', 'name' => 'Customer Relations & Satisfaction Analyst'],
                    ['code' => 'SALES-09', 'name' => 'Key Account Executive Automotive OEM'],
                    ['code' => 'SALES-10', 'name' => 'Admin Sales, Marketing & Invoicing'],
                ],
            ],
            13 => [
                'name' => 'Assembling & Final Inspection',
                'jobs' => [
                    ['code' => 'ASM-01', 'name' => 'Senior Staff Assembling Process & Balancing'],
                    ['code' => 'ASM-02', 'name' => 'Staff Assembly Automation & Poka-Yoke'],
                    ['code' => 'ASM-03', 'name' => 'Foreman Sub-Assembly Line Differential Gear'],
                    ['code' => 'ASM-04', 'name' => 'Foreman Main Assembly Line Transmission'],
                    ['code' => 'ASM-05', 'name' => 'Leader Final Torque & Press-Fit Verification'],
                    ['code' => 'ASM-06', 'name' => 'Leader Final Inspection & Functional Test'],
                    ['code' => 'ASM-07', 'name' => 'Technician Assembly Tooling & Jigs'],
                    ['code' => 'ASM-08', 'name' => 'Operator Semi-Auto Assembling Cell'],
                    ['code' => 'ASM-09', 'name' => 'Inspector End-of-Line Quality Assurance'],
                    ['code' => 'ASM-10', 'name' => 'Admin Assembling Production & Packing'],
                ],
            ],
        ];

        foreach ($groupsData as $number => $data) {
            $group = Group::firstOrCreate(
                ['number' => $number],
                [
                    'name' => $data['name'],
                    'threshold_lagging' => 40,
                ]
            );

            $group->update(['name' => $data['name']]);

            // Jika belum ada jabatan atau di-seed ulang
            if ($group->jobs()->count() === 0) {
                foreach ($data['jobs'] as $order => $jobItem) {
                    Job::create([
                        'group_id' => $group->id,
                        'name' => $jobItem['name'],
                        'sort_order' => $order + 1,
                    ]);
                }
            }
        }
    }
}
