INSERT INTO `sales_staff` (`staffid`, `fullname`, `firstname`, `email`, `active`, `enquiry`, `sale`) VALUES
(1, 'Hello World', 'Hello', 'hello.world@email.com', 1, 0, 0),
(2, 'George Michael', 'George', 'george.michael@email.com', 1, 0, 0),
(3, 'Sarah Lopez', 'Sarah', 'sarah.lopez@email.com', 1, 0, 0),
(4, 'Amy Hope', 'Amy', 'amy.hope@email.com', 1, 0, 0);

INSERT INTO `sales_staff_hours` (`staffid`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`, `holiday`) VALUES
(1, '15:30:00', '15:30:00', '15:30:00', '15:30:00', '15:30:00', NULL, NULL, 0),
(2, NULL, '17:00:00', NULL, '17:00:00', '17:00:00', '17:00:00', NULL, 0),
(3, '19:00:00', '19:00:00', NULL, NULL, '19:00:00', NULL, NULL, 1),
(4, NULL, '19:00:00', '19:00:00', NULL, NULL, '19:00:00', NULL, 0);