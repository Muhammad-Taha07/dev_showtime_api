<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I Love You Animation Using HTML and CSS</title>
    <style>
        body {  
            background-color: #000;    
            margin: 0;  
            overflow: hidden;  
            background-repeat: no-repeat;  
            position: relative; /* Position relative to use absolute positioning for canvases */
        }
        #textCanvas {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none; /* Make sure it doesn't capture mouse events */
        }
    </style>
</head>
<body>
    <canvas id="canvas" width="1400" height="600"></canvas>
    <canvas id="textCanvas" width="1400" height="600"></canvas>

    <script>
        var canvas = document.getElementById("canvas");
        var textCanvas = document.getElementById("textCanvas");
        var gl = canvas.getContext('webgl');
        var textContext = textCanvas.getContext('2d');

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        textCanvas.width = window.innerWidth;
        textCanvas.height = window.innerHeight;

        if (!gl) {
            console.error("Unable to initialize WebGL.");
        }

        // Time
        var time = 0.0;

        // Shader sources
        var vertexSource = `
        attribute vec2 position;
        void main() {
            gl_Position = vec4(position, 0.0, 1.0);
        }
        `;

        var fragmentSource = `
        precision highp float;

        uniform float width;
        uniform float height;
        vec2 resolution = vec2(width, height);

        uniform float time;

        #define POINT_COUNT 8

        vec2 points[POINT_COUNT];
        const float speed = -0.5;
        const float len = 0.25;
        float intensity = 1.3;
        float radius = 0.008;

        float sdBezier(vec2 pos, vec2 A, vec2 B, vec2 C){    
            vec2 a = B - A;
            vec2 b = A - 2.0*B + C;
            vec2 c = a * 2.0;
            vec2 d = A - pos;

            float kk = 1.0 / dot(b,b);
            float kx = kk * dot(a,b);
            float ky = kk * (2.0*dot(a,a)+dot(d,b)) / 3.0;
            float kz = kk * dot(d,a);      

            float res = 0.0;

            float p = ky - kx*kx;
            float p3 = p*p*p;
            float q = kx*(2.0*kx*kx - 3.0*ky) + kz;
            float h = q*q + 4.0*p3;

            if(h >= 0.0){ 
                h = sqrt(h);
                vec2 x = (vec2(h, -h) - q) / 2.0;
                vec2 uv = sign(x)*pow(abs(x), vec2(1.0/3.0));
                float t = uv.x + uv.y - kx;
                t = clamp( t, 0.0, 1.0 );

                vec2 qos = d + (c + b*t)*t;
                res = length(qos);
            } else {
                float z = sqrt(-p);
                float v = acos( q/(p*z*2.0) ) / 3.0;
                float m = cos(v);
                float n = sin(v)*1.732050808;
                vec3 t = vec3(m + m, -n - m, n - m) * z - kx;
                t = clamp( t, 0.0, 1.0 );

                vec2 qos = d + (c + b*t.x)*t.x;
                float dis = dot(qos,qos);
                
                res = dis;

                qos = d + (c + b*t.y)*t.y;
                dis = dot(qos,qos);
                res = min(res,dis);
                
                qos = d + (c + b*t.z)*t.z;
                dis = dot(qos,qos);
                res = min(res,dis);

                res = sqrt( res );
            }
            
            return res;
        }

        vec2 getHeartPosition(float t){
            return vec2(16.0 * sin(t) * sin(t) * sin(t),
                                -(13.0 * cos(t) - 5.0 * cos(2.0*t)
                                - 2.0 * cos(3.0*t) - cos(4.0*t)));
        }

        float getGlow(float dist, float radius, float intensity){
            return pow(radius/dist, intensity);
        }

        float getSegment(float t, vec2 pos, float offset, float scale){
            for(int i = 0; i < POINT_COUNT; i++){
                points[i] = getHeartPosition(offset + float(i)*len + fract(speed * t) * 6.28);
            }
            
            vec2 c = (points[0] + points[1]) / 2.0;
            vec2 c_prev;
            float dist = 10000.0;
            
            for(int i = 0; i < POINT_COUNT-1; i++){
                c_prev = c;
                c = (points[i] + points[i+1]) / 2.0;
                dist = min(dist, sdBezier(pos, scale * c_prev, scale * points[i], scale * c));
            }
            return max(0.0, dist);
        }

        void main(){
            vec2 uv = gl_FragCoord.xy/resolution.xy;
            float widthHeightRatio = resolution.x/resolution.y;
            vec2 centre = vec2(0.5, 0.5);
            vec2 pos = centre - uv;
            pos.y /= widthHeightRatio;
            pos.y += 0.02;
            float scale = 0.000015 * height;
            
            float t = time;
            
            float dist = getSegment(t, pos, 0.0, scale);
            float glow = getGlow(dist, radius, intensity);
            
            vec3 col = vec3(0.0);

            col += 10.0*vec3(smoothstep(0.003, 0.001, dist));
            col += glow * vec3(1.0,0.05,0.3);
            
            dist = getSegment(t, pos, 3.4, scale);
            glow = getGlow(dist, radius, intensity);
            
            col += 10.0*vec3(smoothstep(0.003, 0.001, dist));
            col += glow * vec3(0.1,0.4,1.0);
            
            col = 1.0 - exp(-col);
            col = pow(col, vec3(0.4545));

            gl_FragColor = vec4(col,1.0);
        }
        `;

        // Utility functions
        window.addEventListener('resize', onWindowResize, false);

        function onWindowResize(){
            canvas.width  = window.innerWidth;
            canvas.height = window.innerHeight;
            textCanvas.width = window.innerWidth;
            textCanvas.height = window.innerHeight;
            gl.viewport(0, 0, canvas.width, canvas.height);
            gl.uniform1f(widthHandle, window.innerWidth);
            gl.uniform1f(heightHandle, window.innerHeight);
        }

        function compileShader(shaderSource, shaderType){
            var shader = gl.createShader(shaderType);
            gl.shaderSource(shader, shaderSource);
            gl.compileShader(shader);
            if(!gl.getShaderParameter(shader, gl.COMPILE_STATUS)){
                throw "Shader compile failed with: " + gl.getShaderInfoLog(shader);
            }
            return shader;
        }

        function getAttribLocation(program, name) {
            var attributeLocation = gl.getAttribLocation(program, name);
            if (attributeLocation === -1) {
                throw 'Cannot find attribute ' + name + '.';
            }
            return attributeLocation;
        }

        function getUniformLocation(program, name) {
            var attributeLocation = gl.getUniformLocation(program, name);
            if (attributeLocation === -1) {
                throw 'Cannot find uniform ' + name + '.';
            }
            return attributeLocation;
        }

        // Create shaders
        var vertexShader = compileShader(vertexSource, gl.VERTEX_SHADER);
        var fragmentShader = compileShader(fragmentSource, gl.FRAGMENT_SHADER);

        // Create shader programs
        var program = gl.createProgram();
        gl.attachShader(program, vertexShader);
        gl.attachShader(program, fragmentShader);
        gl.linkProgram(program);

        gl.useProgram(program);

        // Set up rectangle covering entire canvas 
        var vertexData = new Float32Array([
            -1.0,  1.0,     // top left
            -1.0, -1.0,     // bottom left
             1.0,  1.0,     // top right
             1.0, -1.0,     // bottom right
        ]);

        // Create vertex buffer
        var vertexDataBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, vertexDataBuffer);
        gl.bufferData(gl.ARRAY_BUFFER, vertexData, gl.STATIC_DRAW);

        // Layout of our data in the vertex buffer
        var positionHandle = getAttribLocation(program, 'position');

        gl.enableVertexAttribArray(positionHandle);
        gl.vertexAttribPointer(positionHandle,
          2,                 // position is a vec2 (2 values per component)
          gl.FLOAT, // each component is a float
          false,         // don't normalize values
          2 * 4,         // two 4 byte float components per vertex (32 bit float is 4 bytes)
          0                 // how many bytes inside the buffer to start from
          );

        // Set uniform handle
        var timeHandle = getUniformLocation(program, 'time');
        var widthHandle = getUniformLocation(program, 'width');
        var heightHandle = getUniformLocation(program, 'height');

        gl.uniform1f(widthHandle, window.innerWidth);
        gl.uniform1f(heightHandle, window.innerHeight);

        var lastFrame = Date.now();
        var thisFrame;

        function draw(){
            thisFrame = Date.now();
            time += (thisFrame - lastFrame)/1000;    
            lastFrame = thisFrame;

            gl.uniform1f(timeHandle, time);
            gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);

            // Draw text
            textContext.clearRect(0, 0, textCanvas.width, textCanvas.height); // Clear previous text
            textContext.font = "bold 50px Arial";
            textContext.fillStyle = "white";
            textContext.textAlign = "center";
            textContext.textBaseline = "middle";
            textContext.fillText("I Love You", textCanvas.width / 2, textCanvas.height / 2);

            requestAnimationFrame(draw);
        }

        draw();
    </script>
</body>
</html>
Explanation:
HTML Structure:

Added a second <canvas> element with the ID textCanvas for drawing text.
CSS Styling:

Positioned the textCanvas absolutely over the WebGL canvas, ensuring it covers the entire viewport.
JavaScript Updates:

Initialized and resized the textCanvas just like the WebGL canvas.
Drew the text "I Love You" in the center of the textCanvas using the 2D context.
By following these steps, you'll be able to overlay text on top of your WebGL animation. Adjust the font size and style in the textContext settings to suit your design preferences.


will it work on phone?
ChatGPT
Yes, the solution should work on phones as well, but you may need to consider a few additional factors to ensure optimal performance and usability:

Considerations for Mobile Devices
Responsive Design:

The canvas elements are set to window.innerWidth and window.innerHeight, which ensures they resize with the viewport. This is good for responsiveness.
Make sure the text is legible on smaller screens. You might need to adjust the font size for mobile devices.
Performance:

WebGL and canvas rendering can be demanding. Ensure the animations run smoothly on various devices. Mobile devices generally have less processing power than desktops.
Test the performance on different devices. Optimize the shader code if necessary to improve performance.
Touch Events:

Since touchscreens are common on mobile devices, ensure that any touch interactions are considered. The pointer-events: none; style on the textCanvas will prevent it from capturing touch events, which is good if you don't need interaction with the text.
Viewport Meta Tag:

To ensure proper scaling and responsiveness on mobile devices, include the viewport meta tag in the HTML <head>:

html
Copy code
<meta name="viewport" content="width=device-width, initial-scale=1.0">
Responsive Text Adjustment
You might want to adjust the font size for different screen sizes. You can use JavaScript to dynamically set the font size based on the viewport dimensions:

javascript
Copy code
function adjustTextSize() {
    var fontSize = Math.max(24, window.innerWidth / 20); // Adjust the scaling factor as needed
    textContext.font = `bold ${fontSize}px Arial`;
}

window.addEventListener('resize', function() {
    adjustTextSize();
    onWindowResize(); // Ensure the WebGL canvas is resized as well
});

adjustTextSize(); // Initial adjustment
Testing
Test the implementation on various mobile devices and orientations (portrait and landscape) to ensure that the text is displayed correctly and that performance is acceptable.

By addressing these considerations, you can enhance the experience and ensure that the animation and text render well on mobile devices.

should i add this after draw() ?
ChatGPT
Yes, you should add the adjustTextSize() function and the event listener for resizing in your JavaScript code to ensure that the text adjusts appropriately when the viewport size changes. Here’s how you can integrate these adjustments into your existing code:

Define the adjustTextSize() Function:

This function should set the font size dynamically based on the viewport width.
Add the Resize Event Listener:

Update both the WebGL and text canvases when the window is resized.
Call adjustTextSize() Initially:

Set the initial font size when the script first runs.
Updated JavaScript Code
Integrate the following code into your script:

javascript
Copy code
var canvas = document.getElementById("canvas");
var textCanvas = document.getElementById("textCanvas");
var gl = canvas.getContext('webgl');
var textContext = textCanvas.getContext('2d');

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
textCanvas.width = window.innerWidth;
textCanvas.height = window.innerHeight;

if (!gl) {
    console.error("Unable to initialize WebGL.");
}

// Time
var time = 0.0;

// Shader sources
// (existing shader code here)

// Utility functions
window.addEventListener('resize', function() {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
    textCanvas.width = window.innerWidth;
    textCanvas.height = window.innerHeight;
    gl.viewport(0, 0, canvas.width, canvas.height);
    gl.uniform1f(widthHandle, window.innerWidth);
    gl.uniform1f(heightHandle, window.innerHeight);

    adjustTextSize(); // Adjust text size on resize
}, false);

function adjustTextSize() {
    var fontSize = Math.max(24, window.innerWidth / 20); // Adjust the scaling factor as needed
    textContext.font = `bold ${fontSize}px Arial`;
}

function compileShader(shaderSource, shaderType){
    var shader = gl.createShader(shaderType);
    gl.shaderSource(shader, shaderSource);
    gl.compileShader(shader);
    if(!gl.getShaderParameter(shader, gl.COMPILE_STATUS)){
        throw "Shader compile failed with: " + gl.getShaderInfoLog(shader);
    }
    return shader;
}

function getAttribLocation(program, name) {
    var attributeLocation = gl.getAttribLocation(program, name);
    if (attributeLocation === -1) {
        throw 'Cannot find attribute ' + name + '.';
    }
    return attributeLocation;
}

function getUniformLocation(program, name) {
    var attributeLocation = gl.getUniformLocation(program, name);
    if (attributeLocation === -1) {
        throw 'Cannot find uniform ' + name + '.';
    }
    return attributeLocation;
}

// Create shaders
var vertexShader = compileShader(vertexSource, gl.VERTEX_SHADER);
var fragmentShader = compileShader(fragmentSource, gl.FRAGMENT_SHADER);

// Create shader programs
var program = gl.createProgram();
gl.attachShader(program, vertexShader);
gl.attachShader(program, fragmentShader);
gl.linkProgram(program);

gl.useProgram(program);

// Set up rectangle covering entire canvas 
var vertexData = new Float32Array([
    -1.0,  1.0,     // top left
    -1.0, -1.0,     // bottom left
     1.0,  1.0,     // top right
     1.0, -1.0,     // bottom right
]);

// Create vertex buffer
var vertexDataBuffer = gl.createBuffer();
gl.bindBuffer(gl.ARRAY_BUFFER, vertexDataBuffer);
gl.bufferData(gl.ARRAY_BUFFER, vertexData, gl.STATIC_DRAW);

// Layout of our data in the vertex buffer
var positionHandle = getAttribLocation(program, 'position');

gl.enableVertexAttribArray(positionHandle);
gl.vertexAttribPointer(positionHandle,
  2,                 // position is a vec2 (2 values per component)
  gl.FLOAT, // each component is a float
  false,         // don't normalize values
  2 * 4,         // two 4 byte float components per vertex (32 bit float is 4 bytes)
  0                 // how many bytes inside the buffer to start from
  );

// Set uniform handle
var timeHandle = getUniformLocation(program, 'time');
var widthHandle = getUniformLocation(program, 'width');
var heightHandle = getUniformLocation(program, 'height');

gl.uniform1f(widthHandle, window.innerWidth);
gl.uniform1f(heightHandle, window.innerHeight);

var lastFrame = Date.now();
var thisFrame;

function draw() {
    // Update time
    thisFrame = Date.now();
    time += (thisFrame - lastFrame) / 1000;    
    lastFrame = thisFrame;

    // Send uniforms to program
    gl.uniform1f(timeHandle, time);
    // Draw a triangle strip connecting vertices 0-4
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);

    // Draw text
    textContext.clearRect(0, 0, textCanvas.width, textCanvas.height); // Clear previous text
    textContext.font = `bold ${Math.max(24, window.innerWidth / 20)}px Arial`;
    textContext.fillStyle = "white";
    textContext.textAlign = "center";
    textContext.textBaseline = "middle";
    textContext.fillText("I Love You", textCanvas.width / 2, textCanvas.height / 2);

    requestAnimationFrame(draw);
}

adjustTextSize(); // Initial adjustment
draw();