
-- elektor.lua

function os.capture(cmd, raw)
  local f = assert(io.popen(cmd, 'r'))
  local s = assert(f:read('*a'))
  f:close()
  if raw then return s end
  s = string.gsub(s, '^%s+', '')
  s = string.gsub(s, '%s+$', '')
  s = string.gsub(s, '[\n\r]+', ' ')
  return s
end

function wait(n)
  os.execute("sleep " .. tonumber(n))
end

function initADC(n)
  cmd("echo " .. tonumber(n) .. " > /dev/lpc313x_adc")
end

function getADC()
  return tonumber(os.capture("cat /dev/lpc313x_adc"))
end

function cmd(c)
  --print(c)
  os.execute(c)
end

function initButton()
  cmd("echo 11 > /sys/class/gpio/export")
  cmd("echo in > /sys/class/gpio/gpio11/direction")
end

function initLED()
  cmd("echo 3 > /sys/class/gpio/export")
  cmd("echo out > /sys/class/gpio/gpio3/direction")
end 

function initRelay()
  cmd("echo 18 > /sys/class/gpio/export")
  cmd("echo out > /sys/class/gpio/gpio18/direction")
end 


function getButton()
  return os.capture("cat /sys/class/gpio/gpio11/value")
end

function setLED()
  cmd("echo 1 > /sys/class/gpio/gpio3/value")
end

function clearLED()
  cmd("echo 0 > /sys/class/gpio/gpio3/value")
end

function setRelay()
  cmd("echo 1 > /sys/class/gpio/gpio18/value")
end

function clearRelay()
  cmd("echo 0 > /sys/class/gpio/gpio18/value")
end
