import serial, sys, time

if len(sys.argv) == 1:
    print "usage: %s <device>" % sys.argv[0]
    exit(0)

dev = sys.argv[1]
ser = serial.Serial(dev, 115200, timeout=1)

for i in range(20):
    level = i % 2
    print "RTS = %d" % level
    ser.setRTS(level)
    time.sleep(2)
