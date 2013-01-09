
require "elektor"

-- initialize hardware

initButton()
initLED()
initRelay()

-- start main loop


while 1 do
  if getButton() == '1' then print("Button not pressed") else print("Button pressed") end
end
