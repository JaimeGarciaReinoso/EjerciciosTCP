#! /usr/bin/env python3
# vim:fenc=utf-8
#
# Copyright © 2024 jgr <jgr@karraka.local>
#
# Distributed under terms of the MIT license.

"""

"""

import csv
import sys

filename = sys.argv[1]
exercise = int(sys.argv[2])
exerciseID = int(sys.argv[3])
segmentID = int(sys.argv[4])

with open(filename, newline='',encoding='utf-8-sig') as csvfile:
    r = csv.DictReader(csvfile, delimiter=';')
    header = 1
    for row in r:
        if not(row["SN"] == "NULL" and row["AN"] == "NULL" and row["SYN"] == "NULL" and row["ACK"] == "NULL" and row["FIN"] == "NULL" and row["W"] == "NULL" and row["MSS"] == "NULL" and row["MSS"] == "NULL" and row["datalen"] == "NULL"):
            print(f'\tINSERT INTO `Exercises` VALUES ({exerciseID}, {exercise}, {row["Sender"]}, {row["TicID"]}, {segmentID});')
            print(f'\tINSERT INTO `Segments` VALUES ({segmentID}, {row["SN"]}, {row["AN"]}, {row["SYN"]}, {row["ACK"]}, {row["FIN"]}, {row["W"]}, {row["MSS"]}, {row["datalen"]});')
            exerciseID = exerciseID + 1
            segmentID = segmentID + 1
