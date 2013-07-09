
require "elektor"

-- initialize hardware

initLED()

-- start main loop

print("blinking ...")

while 1 do
  setLED()
  wait(1)
  clearLED()
  wait(1)
end
