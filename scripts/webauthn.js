const { startAuthentication, startRegistration } = SimpleWebAuthnBrowser;

const SERVER_URL = "https://passkeys.spcl.one";
const VERIFY_SECRET_URL = "/lending/action/staff/verify-secret";

async function signup() {
  toggleBtnState("webauthn-register-btn", "Processing...");
  const username = $("#username").val();
  const isMobile = navigator.userAgentData.mobile ?? false;

  if (!username) {
    const message = `<div class='alert alert-danger'>Username Required!</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
    toggleBtnState("webauthn-register-btn", "Register Passkey");
    return;
  }

  // 1. Get challenge from server
  const initResponse = await fetch(
    `${SERVER_URL}/registration/start/${username}?isMobile=${isMobile}`
  );
  
  const options = await initResponse.json();
  if (!initResponse.ok) {
    // alert(options.error);
    const message = `<div class='alert alert-danger'>${options.error}</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
    toggleBtnState("webauthn-register-btn", "Register Passkey");
    return;
  }

  // 2. Create passkey

  let registrationJSON;
  try {
    registrationJSON = await startRegistration({ optionsJSON: options });
  } catch (error) {
    console.log({error: error.message});
    const message = `<div class='alert alert-danger'>Oops! Something went wrong. Please try again.</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
    toggleBtnState("webauthn-register-btn", "Register Passkey");
    return;
  }

  // 3. Save passkey in DB
  const verifyResponse = await fetch(
    `${SERVER_URL}/registration/finish/${username}`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(registrationJSON),
    }
  );

  const verifyData = await verifyResponse.json();
  if (!verifyResponse.ok) {
    const message = `<div class='alert alert-danger'>${JSON.stringify(verifyData.error)}</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "10");
    toggleBtnState("webauthn-register-btn", "Register Passkey");
    return;
  }
  if (verifyData.verified) {
    const username = verifyData.username;
    const secret = verifyData.secret;

    const response = await fetch(`${VERIFY_SECRET_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ username, secret }),
    });

    const message = await response.text();

    if (message.includes("ucce")) {
      feedback("SUCCESS", "TOAST", ".feedback_", message, "4");
      toggleBtnState("webauthn-register-btn", "OKAY", false);
    } else {
      toggleBtnState("webauthn-register-btn", "Register Passkey");
    }
  } else {
    const message = `<div class='alert alert-danger'>Failed to register!</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
  }
}

async function login() {
  toggleBtnState("webauthn-login-btn", "Verifying...");
  const username = $("#username").val();

  if (!username) {
    const message = `<div class='alert alert-danger'>Username Required!</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
    toggleBtnState("webauthn-login-btn", "Verify Passkey");
    return;
  }

  // 1. Get challenge from server
  const initResponse = await fetch(
    `${SERVER_URL}/authentication/start/${username}`
  );
  const options = await initResponse.json();
  if (!initResponse.ok) {
    // alert(options.error);
    const message = `<div class='alert alert-danger'>${options.error}</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
    toggleBtnState("webauthn-login-btn", "Verify Passkey");
    return;
  }

  // 2. Get passkey
  let authJSON;
  try {
    authJSON = await startAuthentication({ optionsJSON: options });
  } catch (error) {
    console.log({error: error.message});
    const message = `<div class='alert alert-danger'>Oops! Something went wrong. Please try again.</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
    toggleBtnState("webauthn-login-btn", "Verify Passkey");
    return;
  }

  // 3. Verify passkey with DB
  const verifyResponse = await fetch(
    `${SERVER_URL}/authentication/finish/${username}`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(authJSON),
    }
  );

  const verifyData = await verifyResponse.json();
  if (!verifyResponse.ok) {
    // alert(verifyData.error);
    const message = `<div class='alert alert-danger'>${JSON.stringify(verifyData.error)}</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "10");
    toggleBtnState("webauthn-login-btn", "Verify Passkey");
    return;
  }
  if (verifyData.verified) {
    const username = verifyData.username;
    const secret = verifyData.secret;

    const response = await fetch(`${VERIFY_SECRET_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ username, secret }),
    });

    const message = await response.text();
    if (message.includes("ucce")) {
      feedback("SUCCESS", "TOAST", ".feedback_", message, "4");
      toggleBtnState("webauthn-login-btn", "OKAY", false);
    } else {
      toggleBtnState("webauthn-login-btn", "Verify Passkey");
    }
  } else {
    const message = `<div class='alert alert-danger'>Failed to log in!</div>`;
    feedback("ERROR", "TOAST", ".feedback_", message, "4");
  }
}
