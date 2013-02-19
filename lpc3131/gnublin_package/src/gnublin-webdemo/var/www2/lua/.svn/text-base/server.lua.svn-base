#!/usr/bin/lua

require "posix"

TIMEOUT = 20
SHUTDOWN_CMD = "/sbin/halt"
DEVICE = "/dev/lpc313x_adc"

-- init
adc = io.open("/dev/lpc313x_adc", "w")
adc:write("0\n")
adc:close()

--os.execute("echo 0 > /dev/lpc313x_adc")
timeout = TIMEOUT

-- event loop
while 1 do
  -- determine state
  adc = io.open(DEVICE, "r")
  value = tonumber(adc:read("*line"))
  adc:close()
  if value < 100 then
    print "battery"
    state = "battery"
  else
    print "power"
    state = "power"
  end

  -- check if we want to shutdown
  if state == "battery" then
    if timeout <= 0 then
      os.execute("echo 0 > /sys/class/gpio/gpio3/value")
      os.execute(SHUTDOWN_CMD)
      os.exit()
    else
      timeout = timeout - 1
    end
  else
    timeout = TIMEOUT
  end

  posix.sleep(1)
end
