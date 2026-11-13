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

    def test_dynamic_confidence_calculation(self):
        # Digital DOCX with high completeness
        score_docx = conversion_gate.compute_dynamic_confidence(
            '.docx',
            'Extensive certificate content with more than 150 characters of rich academic text extracted perfectly from digital archive.',
            'Certificate of Outstanding Merit',
            'Department of Science and Technology',
            '2024-05-15',
            'Award'
        )
        self.assertGreaterEqual(score_docx, 0.95)

        # Blurry image document
        score_blur = conversion_gate.compute_dynamic_confidence(
            '.jpg',
            'Low contrast blurred snippet',
            'Participation',
            'ISUFST',
            '2023-01-01',
            'Seminar',
            {'is_blur': True, 'variance': 120.0}
        )
        self.assertLess(score_blur, 0.80)

    def test_check_image_blur_try_catch_safety(self):
        # Non-existent file should gracefully return error without raising exception
        res = conversion_gate.check_image_blur("non_existent_file_path_12345.jpg")
        self.assertIn("is_blur", res)
        self.assertFalse(res["is_blur"])

    def test_check_image_blur_corrupt_file_safety(self):
        # Corrupted / invalid image bytes should safely bypass without crashing
        import tempfile
        with tempfile.NamedTemporaryFile('wb', suffix='.jpg', delete=False) as f:
            f.write(b"NOT_A_VALID_JPEG_HEADER_CORRUPTED_BYTES")
            tmp_path = f.name
        try:
            res = conversion_gate.check_image_blur(tmp_path)
            self.assertIn("is_blur", res)
            self.assertFalse(res["is_blur"])
            self.assertIn("error", res)
        finally:
            if os.path.exists(tmp_path):
                os.remove(tmp_path)

    def test_check_image_blur_synthetic_sharp_and_blur(self):
        from PIL import Image, ImageDraw
        import tempfile
        # 1. Blurry / flat image (variance near 0)
        with tempfile.NamedTemporaryFile('wb', suffix='.jpg', delete=False) as f:
            flat_img = Image.new('L', (100, 100), color=128)
            flat_img.save(f, format='JPEG')
            flat_path = f.name
        # 2. High-frequency sharp image (alternating stripes)
        with tempfile.NamedTemporaryFile('wb', suffix='.jpg', delete=False) as f:
            sharp_img = Image.new('L', (100, 100), color=255)
            draw = ImageDraw.Draw(sharp_img)
            for x in range(0, 100, 4):
                draw.line([(x, 0), (x, 100)], fill=0, width=2)
            sharp_img.save(f, format='JPEG')
            sharp_path = f.name
        try:
            res_blur = conversion_gate.check_image_blur(flat_path)
            self.assertTrue(res_blur["is_blur"])
            self.assertLess(res_blur["variance"], 160.0)

            res_sharp = conversion_gate.check_image_blur(sharp_path)
            self.assertFalse(res_sharp["is_blur"])
            self.assertGreaterEqual(res_sharp["variance"], 160.0)
        finally:
            if os.path.exists(flat_path):
                os.remove(flat_path)
            if os.path.exists(sharp_path):
                os.remove(sharp_path)

    def test_parse_docx_xml_fallback_empty_and_invalid(self):
        import tempfile
        # 0-byte file
        with tempfile.NamedTemporaryFile('wb', suffix='.docx', delete=False) as f:
            tmp_path = f.name
        try:
            txt = conversion_gate.parse_docx_xml_fallback(tmp_path)
            self.assertEqual(txt, "")
        finally:
            if os.path.exists(tmp_path):
                os.remove(tmp_path)

    def test_parse_docx_xml_fallback_with_runs_and_tables(self):
        import tempfile, zipfile
        doc_xml = '''<?xml version="1.0" encoding="UTF-8"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    <w:p>
      <w:r><w:t>Certificate </w:t></w:r>
      <w:r><w:t>of </w:t></w:r>
      <w:r><w:t>Recognition</w:t></w:r>
    </w:p>
    <w:tbl>
      <w:tr>
        <w:tc><w:p><w:r><w:t>Issuer</w:t></w:r></w:p></w:tc>
        <w:tc><w:p><w:r><w:t>Department of Science and Technology</w:t></w:r></w:p></w:tc>
      </w:tr>
    </w:tbl>
  </w:body>
</w:document>'''
        with tempfile.NamedTemporaryFile('wb', suffix='.docx', delete=False) as f:
            tmp_path = f.name
        try:
            with zipfile.ZipFile(tmp_path, 'w') as zf:
                zf.writestr('word/document.xml', doc_xml)
            txt = conversion_gate.parse_docx_xml_fallback(tmp_path)
            self.assertIn("Certificate of Recognition", txt)
            self.assertIn("Issuer | Department of Science and Technology", txt)
        finally:
            if os.path.exists(tmp_path):
                os.remove(tmp_path)

    def test_dynamic_confidence_clamping_and_fallback_filtering(self):
        # Clamping upper bound: maximum possible parameters should clamp to 0.992
        max_score = conversion_gate.compute_dynamic_confidence(
            '.docx', 'Long text ' * 30, 'Certificate of Outstanding Excellence',
            'Department of Information and Communications Technology', '2024-10-15', 'Award'
        )
        self.assertLessEqual(max_score, 0.992)
        self.assertEqual(max_score, 0.992)

        # Clamping lower bound: heavily penalized low-volume blurry document
        min_score = conversion_gate.compute_dynamic_confidence(
            '.jpg', '', '', '', '', 'Other / Uncategorized', {'is_blur': True}
        )
        self.assertGreaterEqual(min_score, 0.35)

        # Fallback titles must not gain the +0.12 title bonus
        score_real_title = conversion_gate.compute_dynamic_confidence(
            '.docx', 'Text ' * 10, 'Certificate of Participation in Cloud Computing',
            'ISUFST', '2024-05-10', 'Training'
        )
        score_fallback_title = conversion_gate.compute_dynamic_confidence(
            '.docx', 'Text ' * 10, 'Academic Document / Certificate',
            'ISUFST', '2024-05-10', 'Training'
        )
        self.assertGreater(score_real_title, score_fallback_title)


if __name__ == '__main__':
    unittest.main()
