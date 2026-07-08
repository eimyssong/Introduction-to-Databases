USE DormitoryDB;

-- 1. 사용자 데이터
INSERT INTO `user` (`user_name`, `student_id`, `password`, `phone_num`, `address`, `Ingredient_ing_id`)
VALUES
('김은송', 202211044, 'pass202211044', 1012340001, 'A동 101호', 1),
('남민영', 202211061, 'pass202211061', 1012340002, 'B동 202호', 2);

-- 2. 식재료 데이터
INSERT INTO `Ingredient` (`ing_id`, `ing_name`, `MFG_DATETIME`, `storage_loc`, `EXP_DATETIME`, `state`, `user_student_id`)
VALUES
(1, '김치찌개 재료', DATE_SUB(NOW(), INTERVAL 5 DAY), 'A1', DATE_ADD(NOW(), INTERVAL 20 DAY), '정상', 202211044),
(2, '닭가슴살', DATE_SUB(NOW(), INTERVAL 30 DAY), 'A2', DATE_SUB(NOW(), INTERVAL 3 DAY), '폐기대상', 202211044),
(3, '피자', DATE_SUB(NOW(), INTERVAL 2 DAY), 'B1', DATE_ADD(NOW(), INTERVAL 26 DAY), '정상', 202211061),
(4, '아이스크림', DATE_SUB(NOW(), INTERVAL 10 DAY), 'C1', DATE_ADD(NOW(), INTERVAL 90 DAY), '정상', 202211061);

-- 3. 냉장고 데이터
INSERT INTO `Refrigerator` (`ref_id`, `Zone`, `Ingredient_ing_id`, `user_student_id`)
VALUES
(1, 'A1', 1, 202211044),
(2, 'A2', 2, 202211044),
(3, 'B1', 3, 202211061),
(4, 'C1', 4, 202211061);

-- 4. 관리자 데이터
INSERT INTO `advisor` (`advisor_id`, `advisor_name`, `advisor_password`, `Ingredient_ing_id`, `user_student_id`)
VALUES
(1, '관리자1', 'adminpass', 1, 202211044);

-- 5. 관리자-냉장고 연결
INSERT INTO `advisor_has_Refrigerator` (`advisor_advisor_id`, `Refrigerator_ref_id`)
VALUES
(1, 1), (1, 2), (1, 3), (1, 4);
