import os

DEVICE = '/dev/lpc313x_adc'

def select_gpa1():
   fd = os.open(DEVICE, os.O_RDWR)
   os.write(fd, "0x0001")
   os.close(fd)

def get_adc():
   fd = os.open(DEVICE, os.O_RDONLY)
   av = os.read(fd, 256)
   os.close(fd)
   return av[:-1]  # strip off trailing '\n'

if __name__ == "__main__":
   select_gpa1()
   ad_val = get_adc()
   print ad_val



