import time

def d2s(v):
    ''' v is between 0 and 4095 '''
    b0 = v & 0xff
    b1 = (v & 0xfff) >> 8
    return chr(b0) + chr(b1)

def pwm_raw(v):
    if v < 0 or v > 4095:
        print "v is out of range"
        return
    p = open("/dev/lpc313x_pwm", "wb")
    s = d2s(v)
    p.write(s)
    p.flush()
    p.close()

def pwm(r):
   '''Set PWM output to 0.0% <= r <= 100.0%'''
   v = int( r/100.0 * 4095 )
   pwm_raw(v)

if __name__ == "__main__":
    pwm(50)
