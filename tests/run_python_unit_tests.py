#!/usr/bin/env python3
"""
SecureFaculty Python Engine Micro-Unit Test Runner
Discovers and executes all micro-unit test suites for Python backend modules.
"""

import unittest
import sys
import os

if __name__ == '__main__':
    test_dir = os.path.join(os.path.dirname(__file__), 'Unit/PythonEngine')
    print("========================================================================")
    print(" SECUREFACULTY: PYTHON ENGINE MICRO-UNIT TEST SUITE")
    print(f" Discovering unit tests in: {test_dir}")
    print("========================================================================\n")

    loader = unittest.TestLoader()
    suite = loader.discover(start_dir=test_dir, pattern='test_*.py')

    runner = unittest.TextTestRunner(verbosity=2)
    result = runner.run(suite)

    print("\n========================================================================")
    print(f" Tests Run:    {result.testsRun}")
    print(f" Errors:       {len(result.errors)}")
    print(f" Failures:     {len(result.failures)}")
    status = "SUCCESS" if result.wasSuccessful() else "FAILED"
    print(f" Overall:      {status}")
    print("========================================================================")

    sys.exit(0 if result.wasSuccessful() else 1)
