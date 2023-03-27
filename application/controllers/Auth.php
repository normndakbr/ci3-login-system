<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('form');
    }

    public function index()
    {
        $this->form_validation->set_rules('email_login', 'Email', 'required|trim|valid_email', [
            'required' => 'Email tidak boleh kosong.',
        ]);
        $this->form_validation->set_rules('password_login', 'Password', 'trim|required', [
            'required' => 'Password tidak boleh kosong.',
        ]);

        if ($this->form_validation->run() == false) {
            $data['title'] = 'User Login';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            $this->_login();
        }
    }

    private function _login()
    {
        $email = $this->input->post('email_login');
        $password = $this->input->post('password_login');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if ($user) {
            if ($user['is_active'] == 1) {
                if (password_verify($password, $user['password'])) {
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id'],
                    ];

                    $this->session->set_userdata($data);
                    redirect('user');
                } else {
                    $this->session->set_flashdata('register_message', '
                    <div class="alert alert-danger" role="alert">
                        Password anda salah.
                    </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('register_message', '
                <div class="alert alert-warning" role="alert">
                    Akun belum diaktivasi, silahkan hubungi admin.
                </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('register_message', '
                <div class="alert alert-danger" role="alert">
                    Akun tidak ditemukan.
                </div>');
            redirect('auth');
        }
    }

    public function registration()
    {
        $this->form_validation->set_rules('name', 'Name', 'required|trim', [
            'required' => 'Nama tidak boleh kosong.',
        ]);
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
            'required' => 'Email tidak boleh kosong.',
            'is_unique' => 'Email sudah digunakan.',
        ]);
        $this->form_validation->set_rules('password1', 'Password1', 'required|trim|min_length[3]|matches[password2]', [
            'min_length' => 'Password terlalu pendek, minimal 3 karakter',
            'required' => 'Password tidak boleh kosong.',
            'matches' => 'Password dan Konfirmasi Password tidak sama.',
        ]);
        $this->form_validation->set_rules('password2', 'Password2', 'required|trim|matches[password1]', [
            'required' => 'Konfirmasi password tidak boleh kosong.',
            'matches' => 'Password dan Konfirmasi Password tidak sama.',
        ]);

        if ($this->form_validation->run() == false) {
            $data['title'] = 'User Registration';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $data = [
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()
            ];

            $this->db->insert('user', $data);
            $this->session->set_flashdata('register_message', '
            <div class="alert alert-success" role="alert">
                Akun baru berhasil dibuat!
            </div>');
            redirect('auth');
        }
    }
}
