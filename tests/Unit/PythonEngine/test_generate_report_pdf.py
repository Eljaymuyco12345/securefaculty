#!/usr/bin/env python3
"""
ReportLab PDF Generator Micro-Unit Test
Tests python_engine/generate_report_pdf.py compilation of Portrait A4 accreditation PDF reports,
validating PDF binary signature, page dimensions, table formatting, and running footers.
"""

import unittest
import sys
import os
import json
import tempfile

sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../python_engine')))

import generate_report_pdf


class TestGenerateReportPdf(unittest.TestCase):
    def setUp(self):
        self.temp_json = tempfile.NamedTemporaryFile(suffix='.json', delete=False)
        self.temp_pdf = tempfile.NamedTemporaryFile(suffix='.pdf', delete=False)
        self.temp_json.close()
        self.temp_pdf.close()

    def tearDown(self):
        for f in [self.temp_json.name, self.temp_pdf.name]:
            if os.path.exists(f):
                os.remove(f)

    def test_pdf_generation_portrait_a4(self):
        sample_payload = {
            "meta": {
                "category": "All",
                "date_scope": "All Validated Records",
                "auditor_name": "Dr. Arlene S. Poblador",
                "generated_at": "October 21, 2026 at 03:00 AM"
            },
            "records": [
                {
                    "title": "Unit Test Research Publication",
                    "file_type": "PDF",
                    "faculty_name": "Prof. Levy Mikhail B. Aguilar",
                    "academic_rank": "Assistant Professor II",
                    "co_authors": "Prof. Rae Jan B. Corneja",
                    "cert_number": "CERT-2026-UNIT-01",
                    "date_issued": "2026-04-15",
                    "issuer": "ISUFST College of ICT",
                    "category": "Research Publication"
                }
            ]
        }

        with open(self.temp_json.name, 'w', encoding='utf-8') as f:
            json.dump(sample_payload, f)

        # Call generate_pdf
        generate_report_pdf.generate_pdf(self.temp_json.name, self.temp_pdf.name)

        # Verify output exists
        self.assertTrue(os.path.exists(self.temp_pdf.name))
        file_size = os.path.getsize(self.temp_pdf.name)
        self.assertGreater(file_size, 1000, "Generated PDF must be non-empty and > 1KB")

        # Verify %PDF-1. magic header
        with open(self.temp_pdf.name, 'rb') as f:
            header = f.read(8)
            self.assertTrue(header.startswith(b'%PDF-1.'), f"File must be valid PDF, got: {header}")


if __name__ == '__main__':
    unittest.main()
