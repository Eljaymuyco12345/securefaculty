#!/usr/bin/env python3
"""
Conversion Gate Micro-Unit Test
Tests python_engine/conversion_gate.py date normalization, anti-hallucination logic,
title extraction, issuer recognition, and academic category inference.
"""

import unittest
import sys
import os

# Add python_engine to sys.path
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../python_engine')))

import conversion_gate


class TestConversionGate(unittest.TestCase):
    def test_normalize_date_iso_standard(self):
        self.assertEqual(conversion_gate.normalize_date_iso("2026-05-07"), "2026-05-07")
        self.assertEqual(conversion_gate.normalize_date_iso("2023/10/24"), "2023-10-24")
        self.assertEqual(conversion_gate.normalize_date_iso("2024.02.29"), "2024-02-29")

    def test_normalize_date_philippine_phrasing(self):
        self.assertEqual(conversion_gate.normalize_date_iso("15th day of December, 2022"), "2022-12-15")
        self.assertEqual(conversion_gate.normalize_date_iso("1st day of June, 2021"), "2021-06-01")
        self.assertEqual(conversion_gate.normalize_date_iso("22nd day of March 2024"), "2024-03-22")
        self.assertEqual(conversion_gate.normalize_date_iso("3rd day of August, 2023"), "2023-08-03")

    def test_normalize_date_month_first(self):
        self.assertEqual(conversion_gate.normalize_date_iso("October 24, 2023"), "2023-10-24")
        self.assertEqual(conversion_gate.normalize_date_iso("January 5, 2026"), "2026-01-05")

    def test_normalize_date_slash_formats(self):
        self.assertEqual(conversion_gate.normalize_date_iso("10/24/2023"), "2023-10-24")
        self.assertEqual(conversion_gate.normalize_date_iso("25/12/2022"), "2022-12-25")

    def test_normalize_date_anti_hallucination_empty_inputs(self):
        self.assertEqual(conversion_gate.normalize_date_iso(""), "")
        self.assertEqual(conversion_gate.normalize_date_iso("   "), "")
        self.assertEqual(conversion_gate.normalize_date_iso(None), "")
        self.assertEqual(conversion_gate.normalize_date_iso("not_a_valid_date"), "")

    def test_normalize_date_out_of_range_years(self):
        self.assertEqual(conversion_gate.normalize_date_iso("1820-05-12"), "")
        self.assertEqual(conversion_gate.normalize_date_iso("2150-01-01"), "")

    def test_extract_dates_from_body_text(self):
        text = "This is to certify that Juan Dela Cruz completed training on this 15th day of December, 2022 at ISUFST."
        extracted = conversion_gate.extract_dates(text)
        self.assertEqual(extracted, "2022-12-15")

    def test_extract_dates_anti_hallucination(self):
        text = "Generic participation document without any calendar date mentioned anywhere in body."
        extracted = conversion_gate.extract_dates(text)
        self.assertEqual(extracted, "")

    def test_extract_title(self):
        text = "Republic of the Philippines\nCertificate of Completion\nPresented to Mikhail Aguilar"
        lines = text.split("\n")
        title = conversion_gate.extract_title(text, lines)
        self.assertEqual(title, "Certificate of Completion")

    def test_extract_issuer(self):
        text = "Issued by the Department of Information and Communications Technology (DICT) Region VI"
        lines = text.split("\n")
        issuer = conversion_gate.extract_issuer(text, lines)
        self.assertEqual(issuer, "Department of Information and Communications Technology")

    def test_infer_category(self):
        self.assertEqual(conversion_gate.infer_category("International Journal IEEE Conference Paper", "Paper Title"), "Research Publication")
        self.assertEqual(conversion_gate.infer_category("Hands-on Intensive Coding Bootcamp Training", "Certificate of Training"), "Training")
        self.assertEqual(conversion_gate.infer_category("National Symposium on Cloud Technologies", "Seminar Participation"), "Seminar")
        self.assertEqual(conversion_gate.infer_category("Outstanding Educator Recognition Honor", "Plaque of Appreciation"), "Award")
        self.assertEqual(conversion_gate.infer_category("Interactive Learning Module and Courseware", "Syllabus Guide"), "Instructional Material")


if __name__ == '__main__':
    unittest.main()
